package Services;

import groq4j.builders.ChatCompletionRequestBuilder;
import groq4j.models.chat.ChatCompletionRequest;
import groq4j.models.chat.ChatCompletionResponse;
import groq4j.models.common.Message;
import groq4j.services.ChatService;
import groq4j.services.ChatServiceImpl;
import io.github.cdimascio.dotenv.Dotenv;

import java.util.List;

public class GroqChatService {

    private static final Dotenv dotenv = Dotenv.configure()
            .directory("./")
            .ignoreIfMalformed()
            .ignoreIfMissing()
            .load();

    private static final String GROQ_API_KEY = dotenv.get("GROQ_API_KEY");
    private static final String MODEL = "llama-3.3-70b-versatile";

    private final ChatService chatService;

    public GroqChatService() {
        if (GROQ_API_KEY == null || GROQ_API_KEY.isEmpty()) {
            System.err.println("✗ GROQ_API_KEY not set in .env");
            throw new IllegalStateException("GROQ_API_KEY is required");
        }

        this.chatService = ChatServiceImpl.create(GROQ_API_KEY);
        System.out.println("✓ Groq Chat Service initialized with model: " + MODEL);
    }

    public String sendSimpleMessage(String systemPrompt, String userMessage) {
        try {
            System.out.println("⚙ Sending message to Groq API...");

            ChatCompletionRequest request = ChatCompletionRequestBuilder.create(MODEL)
                    .systemMessage(systemPrompt)
                    .userMessage(userMessage)
                    .temperature(0.7)
                    .maxCompletionTokens(1000)
                    .build();

            ChatCompletionResponse response = chatService.createCompletion(request);

            if (response.choices() != null && !response.choices().isEmpty()) {
                String content = response.choices().get(0).message().content().orElse("");
                System.out.println("✓ Received AI response (" + content.length() + " chars)");
                return content;
            }

            return "Error: No response from AI";

        } catch (Exception e) {
            System.err.println("✗ Failed to get AI response: " + e.getMessage());
            e.printStackTrace();
            return "Error: " + e.getMessage();
        }
    }

    public String sendMessage(List<Message> messages, double temperature) {
        try {
            System.out.println("⚙ Sending conversation to Groq API (" + messages.size() + " messages)...");

            ChatCompletionRequest request = ChatCompletionRequestBuilder.create(MODEL)
                    .messages(messages)
                    .temperature(temperature)
                    .maxCompletionTokens(1000)
                    .build();

            ChatCompletionResponse response = chatService.createCompletion(request);

            if (response.choices() != null && !response.choices().isEmpty()) {
                String content = response.choices().getFirst().message().content().orElse("");
                System.out.println("✓ Received AI response");
                return content;
            }

            return "Error: No response from AI";

        } catch (Exception e) {
            System.err.println("✗ Failed to get AI response: " + e.getMessage());
            e.printStackTrace();
            return "Error: Failed to connect to AI. Please try again.";
        }
    }

    public static String createCompanyRepPrompt(String companyName, String country,
                                                String industry, String address) {
        return String.format(
                """
                        You are a professional business representative for %s, a company based in %s. \
                        Your company specializes in %s. \
                        %s\
                        
                        
                        Your role is to:
                        - Respond professionally to business inquiries
                        - Provide information about your company's products and services
                        - Discuss potential partnership opportunities
                        - Answer questions about export capabilities and trade terms
                        - Be helpful, friendly, and business-oriented
                        
                        Keep responses concise (2-3 sentences) and professional. \
                        If you don't know specific details, politely indicate you can provide that information later.""",
                companyName,
                country,
                industry,
                address != null && !address.isEmpty() ? "Your office is located at: " + address + "." : ""
        );
    }
}