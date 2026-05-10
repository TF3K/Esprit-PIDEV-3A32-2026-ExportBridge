package GUI;

import Entities.Company;
import Services.GroqChatService;
import groq4j.models.common.Message;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import lombok.Setter;

import java.io.IOException;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;

public class CompanyChatViewController {

    @FXML private Button backButton;
    @FXML private Text companyFlag;
    @FXML private Text companyName;
    @FXML private Text companyInfo;
    @FXML private ScrollPane chatScrollPane;
    @FXML private VBox messagesContainer;
    @FXML private TextField messageInput;
    @FXML private Button sendButton;
    @FXML private HBox loadingIndicator;

    private Company company;
    private List<Message> conversationHistory;
    private String systemPrompt;
    private GroqChatService groqService;
    @Setter
    private StackPane contentArea;
    @Setter
    private Runnable onBackCallback;

    @FXML
    public void initialize() {
        conversationHistory = new ArrayList<>();

        try {
            groqService = new GroqChatService();
            System.out.println("✓ Groq service initialized");
        } catch (IllegalStateException e) {
            System.err.println("✗ Groq API key not configured");
            showError("Groq API key not configured. Please add GROQ_API_KEY to .env file.");
            return;
        }

        messagesContainer.heightProperty().addListener((obs, oldVal, newVal) -> {
            chatScrollPane.setVvalue(1.0);
        });
    }

    public void setCompany(Company company) {
        this.company = company;
        setupChat();
    }

    private void setupChat() {
        companyFlag.setText(getCountryFlag(company.getCountry()));
        companyName.setText("Chat with " + company.getCompanyName());
        companyInfo.setText(company.getCountry() + " • " +
                (company.getDomain() != null ? company.getDomain() : "General Trading"));

        systemPrompt = GroqChatService.createCompanyRepPrompt(
                company.getCompanyName(),
                company.getCountry(),
                company.getDomain() != null ? company.getDomain() : "international trade",
                company.getAddress()
        );

        conversationHistory.add(Message.system(systemPrompt));

        addAIMessage("Hello! I'm the business representative for " + company.getCompanyName() + ". " +
                "How can I assist you with your business inquiry today?");
    }

    @FXML
    private void handleSendMessage() {
        String message = messageInput.getText().trim();

        if (message.isEmpty()) {
            return;
        }

        addUserMessage(message);

        messageInput.clear();

        messageInput.setDisable(true);
        sendButton.setDisable(true);
        loadingIndicator.setManaged(true);
        loadingIndicator.setVisible(true);

        conversationHistory.add(Message.user(message));

        new Thread(() -> {
            try {
                String response = groqService.sendMessage(conversationHistory, 0.7);

                conversationHistory.add(Message.assistant(response));

                Platform.runLater(() -> {
                    addAIMessage(response);
                    messageInput.setDisable(false);
                    sendButton.setDisable(false);
                    loadingIndicator.setManaged(false);
                    loadingIndicator.setVisible(false);
                    messageInput.requestFocus();
                });

            } catch (Exception e) {
                System.err.println("✗ Error getting AI response: " + e.getMessage());
                e.printStackTrace();

                Platform.runLater(() -> {
                    addAIMessage("Sorry, I encountered an error. Please try again.");
                    messageInput.setDisable(false);
                    sendButton.setDisable(false);
                    loadingIndicator.setManaged(false);
                    loadingIndicator.setVisible(false);
                });
            }
        }).start();
    }

    @FXML
    private void handleBack() {
        System.out.println("⚙ Back button clicked from chat view");

        if (onBackCallback != null) {
            System.out.println("✓ Using callback");
            onBackCallback.run();
        } else if (contentArea != null) {
            System.out.println("⚙ Using direct content area");
            navigateToCompaniesBrowser();
        } else {
            System.err.println("✗ No navigation method available");
        }
    }

    private void navigateToCompaniesBrowser() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/companies-browser-view.fxml"));
            Parent view = loader.load();

            CompaniesBrowserViewController controller = loader.getController();
            if (company != null) {
                Entities.Market market = new Entities.Market();
                market.setName(company.getCountry());
                market.setCountryCode(getCountryCodeFromName(company.getCountry()));
                controller.setMarket(market);
                controller.setContentArea(contentArea);
            }

            contentArea.getChildren().clear();
            contentArea.getChildren().add(view);

            System.out.println("✓ Navigated back to companies browser");

        } catch (IOException e) {
            System.err.println("✗ Failed to navigate back");
            e.printStackTrace();
        }
    }

    private void addUserMessage(String message) {
        HBox messageBox = createMessageBox(message, true);
        messagesContainer.getChildren().add(messageBox);
    }

    private void addAIMessage(String message) {
        HBox messageBox = createMessageBox(message, false);
        messagesContainer.getChildren().add(messageBox);
    }

    private HBox createMessageBox(String message, boolean isUser) {
        HBox container = new HBox();
        container.setAlignment(isUser ? Pos.CENTER_RIGHT : Pos.CENTER_LEFT);

        VBox messageBox = new VBox(4);
        messageBox.getStyleClass().add(isUser ? "user-message" : "ai-message");
        messageBox.setMaxWidth(500);

        Text messageText = new Text(message);
        messageText.setWrappingWidth(480);
        messageText.getStyleClass().add("message-text");

        Text timestamp = new Text(LocalDateTime.now().format(DateTimeFormatter.ofPattern("HH:mm")));
        timestamp.getStyleClass().add("message-timestamp");

        messageBox.getChildren().addAll(messageText, timestamp);

        if (!isUser) {
            Text icon = new Text("🤖");
            icon.setStyle("-fx-font-size: 24px;");
            HBox.setMargin(icon, new Insets(0, 12, 0, 0));
            container.getChildren().addAll(icon, messageBox);
        } else {
            container.getChildren().add(messageBox);
        }

        return container;
    }

    private void showError(String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Error");
        alert.setHeaderText("Configuration Error");
        alert.setContentText(message);
        alert.showAndWait();
    }

    private String getCountryFlag(String country) {
        return switch (country) {
            case "France" -> "🇫🇷";
            case "Germany" -> "🇩🇪";
            case "Italy" -> "🇮🇹";
            case "Spain" -> "🇪🇸";
            case "Belgium" -> "🇧🇪";
            case "Netherlands" -> "🇳🇱";
            case "Portugal" -> "🇵🇹";
            case "Greece" -> "🇬🇷";
            case "United Kingdom" -> "🇬🇧";
            case "China" -> "🇨🇳";
            case "Singapore" -> "🇸🇬";
            case "Thailand" -> "🇹🇭";
            case "Malaysia" -> "🇲🇾";
            case "Indonesia" -> "🇮🇩";
            case "India" -> "🇮🇳";
            case "Taiwan" -> "🇹🇼";
            case "South Africa" -> "🇿🇦";
            case "United Arab Emirates" -> "🇦🇪";
            default -> "🌍";
        };
    }

    private String getCountryCodeFromName(String country) {
        return switch (country) {
            case "France" -> "FR";
            case "Germany" -> "DE";
            case "Italy" -> "IT";
            case "Spain" -> "ES";
            case "Belgium" -> "BE";
            case "Netherlands" -> "NL";
            case "Portugal" -> "PT";
            case "Greece" -> "GR";
            case "United Kingdom" -> "GB";
            case "China" -> "CN";
            case "Singapore" -> "SG";
            case "Thailand" -> "TH";
            case "Malaysia" -> "MY";
            case "Indonesia" -> "ID";
            case "India" -> "IN";
            case "Taiwan" -> "TW";
            case "South Africa" -> "ZA";
            case "United Arab Emirates" -> "AE";
            default -> "XX";
        };
    }
}