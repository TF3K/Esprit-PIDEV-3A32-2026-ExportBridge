package Services;

import io.github.cdimascio.dotenv.Dotenv;
import io.mailtrap.client.MailtrapClient;
import io.mailtrap.config.MailtrapConfig;
import io.mailtrap.factory.MailtrapClientFactory;
import io.mailtrap.model.request.emails.Address;
import io.mailtrap.model.request.emails.MailtrapMail;
import lombok.Data;

import java.io.*;
import java.nio.file.*;
import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.List;
import java.util.concurrent.ConcurrentHashMap;

public class EmailService {
    private static final Dotenv dotenv = Dotenv.configure()
            .directory("./")
            .ignoreIfMalformed()
            .ignoreIfMissing()
            .load();

    private static final String MAILTRAP_TOKEN = dotenv.get("MAILTRAP_API_KEY");
    private static final String FROM_EMAIL = "noreply@exportbridge.com";
    private static final String FROM_NAME = "ExportBridge Support";

    private static final ConcurrentHashMap<String, PasswordResetToken> resetTokens = new ConcurrentHashMap<>();

    private static final String TOKENS_DIR = System.getProperty("user.home") + "/.exportbridge/reset-tokens/";
    private static final int TOKEN_EXPIRY_HOURS = 1;

    static {
        loadAllTokens();
    }

    public static boolean sendPasswordResetEmail(String toEmail, String resetToken) {
        System.out.println("=== DEBUG EMAIL CONFIGURATION ===");
        System.out.println("MAILTRAP_TOKEN loaded: " + (MAILTRAP_TOKEN != null ? "Yes" : "No"));
        System.out.println("Token length: " + (MAILTRAP_TOKEN != null ? MAILTRAP_TOKEN.length() : 0));
        System.out.println("Token starts with: " + (MAILTRAP_TOKEN != null ? MAILTRAP_TOKEN.substring(0, Math.min(10, MAILTRAP_TOKEN.length())) : "null"));
        System.out.println("================================");

        if (MAILTRAP_TOKEN == null || MAILTRAP_TOKEN.isEmpty()) {
            System.err.println("✗ Mailtrap not configured! Set MAILTRAP_API_KEY in .env");
            return false;
        }

        try {
            final MailtrapConfig config = new MailtrapConfig.Builder()
                    .sandbox(true)
                    .inboxId(4403511L)
                    .token(MAILTRAP_TOKEN)
                    .build();

            final MailtrapClient client = MailtrapClientFactory.createMailtrapClient(config);

            Address from = new Address(FROM_EMAIL, FROM_NAME);
            Address to = new Address(toEmail);

            String emailBody = createResetEmailBody(resetToken);

            MailtrapMail mail = MailtrapMail.builder()
                    .from(from)
                    .to(List.of(to))
                    .subject("ExportBridge - Password Reset Request")
                    .html(emailBody)
                    .text("Please enable HTML to view this email. Token: " + resetToken) // Fallback text
                    .build();

            client.send(mail);

            System.out.println("✓ Password reset email sent to: " + toEmail);
            return true;

        } catch (Exception e) {
            System.err.println("✗ Failed to send email: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }

    public static String generateResetToken(String email, Long managerId) {
        String token = generateShortToken();

        PasswordResetToken resetToken = new PasswordResetToken();
        resetToken.setToken(token);
        resetToken.setEmail(email);
        resetToken.setManagerId(managerId);
        resetToken.setExpiryTime(LocalDateTime.now().plusHours(TOKEN_EXPIRY_HOURS));

        resetTokens.put(token, resetToken);

        saveTokenToDisk(resetToken);

        cleanupExpiredTokens();

        System.out.println("✓ Reset token generated: " + token);
        return token;
    }

    private static String generateShortToken() {
        return String.format("%06d", (int)(Math.random() * 1000000));
    }

    public static PasswordResetToken validateResetToken(String token) {
        if (token == null || token.trim().isEmpty()) {
            System.out.println("✗ Token is null or empty");
            return null;
        }

        PasswordResetToken resetToken = getToken(token.trim());

        if (resetToken == null) {
            System.out.println("✗ Token not found: " + token);
            return null;
        }

        long minutesSinceCreation = ChronoUnit.MINUTES.between(
                resetToken.getExpiryTime().minusHours(TOKEN_EXPIRY_HOURS),
                LocalDateTime.now()
        );

        if (resetToken.getExpiryTime().isBefore(LocalDateTime.now())) {
            System.out.println("✗ Token expired (" + minutesSinceCreation + " minutes old)");
            invalidateResetToken(token);
            return null;
        }

        System.out.println("✓ Token is valid (" + minutesSinceCreation + " minutes old)");
        return resetToken;
    }

    private static PasswordResetToken getToken(String token) {
        PasswordResetToken resetToken = resetTokens.get(token);

        if (resetToken == null) {
            System.out.println("⚠ Token not in memory, loading from disk: " + token);
            resetToken = loadTokenFromDisk(token);
            if (resetToken != null) {
                resetTokens.put(token, resetToken);
                System.out.println("✓ Token loaded from disk");
            }
        }

        return resetToken;
    }

    public static void invalidateResetToken(String token) {
        resetTokens.remove(token);
        deleteTokenFromDisk(token);
        System.out.println("✓ Token invalidated: " + token);
    }

    private static void cleanupExpiredTokens() {
        LocalDateTime now = LocalDateTime.now();
        resetTokens.entrySet().removeIf(entry -> {
            boolean expired = entry.getValue().getExpiryTime().isBefore(now);
            if (expired) {
                deleteTokenFromDisk(entry.getKey());
            }
            return expired;
        });
    }

    private static void saveTokenToDisk(PasswordResetToken token) {
        try {
            File tokensDir = new File(TOKENS_DIR);
            if (!tokensDir.exists()) {
                tokensDir.mkdirs();
            }

            String filename = TOKENS_DIR + token.getToken() + ".token";
            try (ObjectOutputStream oos = new ObjectOutputStream(new FileOutputStream(filename))) {
                oos.writeObject(token);
            }

            System.out.println("✓ Reset token saved to disk: " + filename);

        } catch (IOException e) {
            System.err.println("✗ Failed to save token to disk: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private static PasswordResetToken loadTokenFromDisk(String token) {
        try {
            String filename = TOKENS_DIR + token + ".token";
            File tokenFile = new File(filename);

            if (!tokenFile.exists()) {
                return null;
            }

            try (ObjectInputStream ois = new ObjectInputStream(new FileInputStream(filename))) {
                PasswordResetToken resetToken = (PasswordResetToken) ois.readObject();
                System.out.println("✓ Token loaded from: " + filename);
                return resetToken;
            }

        } catch (IOException | ClassNotFoundException e) {
            System.err.println("✗ Failed to load token from disk: " + e.getMessage());
            return null;
        }
    }

    private static void deleteTokenFromDisk(String token) {
        try {
            String filename = TOKENS_DIR + token + ".token";
            boolean deleted = Files.deleteIfExists(Paths.get(filename));
            if (deleted) {
                System.out.println("✓ Token file deleted: " + filename);
            }
        } catch (IOException e) {
            System.err.println("✗ Failed to delete token file: " + e.getMessage());
        }
    }

    private static void loadAllTokens() {
        try {
            File tokensDir = new File(TOKENS_DIR);
            if (!tokensDir.exists()) {
                System.out.println("⚠ Reset tokens directory does not exist: " + TOKENS_DIR);
                return;
            }

            File[] tokenFiles = tokensDir.listFiles((dir, name) -> name.endsWith(".token"));
            if (tokenFiles == null || tokenFiles.length == 0) {
                System.out.println("⚠ No token files found in: " + TOKENS_DIR);
                return;
            }

            System.out.println("⚙ Found " + tokenFiles.length + " token file(s)");

            int loaded = 0;
            LocalDateTime now = LocalDateTime.now();

            for (File file : tokenFiles) {
                String token = file.getName().replace(".token", "");
                PasswordResetToken resetToken = loadTokenFromDisk(token);

                if (resetToken != null) {
                    if (resetToken.getExpiryTime().isAfter(now)) {
                        resetTokens.put(token, resetToken);
                        loaded++;

                        long minutesRemaining = ChronoUnit.MINUTES.between(now, resetToken.getExpiryTime());
                        System.out.println("✓ Token loaded: " + token + " (expires in " + minutesRemaining + " min)");
                    } else {
                        System.out.println("✗ Token expired, deleting: " + token);
                        file.delete();
                    }
                } else {
                    System.out.println("✗ Invalid token, deleting: " + file.getName());
                    file.delete();
                }
            }

            if (loaded > 0) {
                System.out.println("✓ Loaded " + loaded + " active reset token(s) from disk");
            } else {
                System.out.println("⚠ No valid reset tokens were loaded");
            }

        } catch (Exception e) {
            System.err.println("✗ Failed to load tokens: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private static String createResetEmailBody(String token) {
        return "<!DOCTYPE html>" +
                "<html>" +
                "<head>" +
                "<meta charset='UTF-8'>" +
                "<meta name='viewport' content='width=device-width, initial-scale=1.0'>" +
                "<style>" +
                "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f5f5f7; }" +
                ".container { max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }" +
                ".header { background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: white; padding: 40px 30px; text-align: center; }" +
                ".header h1 { margin: 0; font-size: 28px; font-weight: 600; }" +
                ".logo { font-size: 48px; margin-bottom: 10px; }" +
                ".content { padding: 40px 30px; }" +
                ".content h2 { color: #1a1d29; font-size: 20px; margin: 0 0 20px 0; }" +
                ".content p { color: #6b7280; font-size: 16px; margin: 0 0 20px 0; }" +
                ".token-container { background: #f3f4f6; border: 2px dashed #d1d5db; border-radius: 8px; padding: 30px; text-align: center; margin: 30px 0; }" +
                ".token { font-family: 'Courier New', monospace; font-size: 36px; font-weight: bold; color: #4f46e5; letter-spacing: 8px; }" +
                ".warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 4px; }" +
                ".warning-icon { font-size: 18px; margin-right: 8px; }" +
                ".warning-text { color: #92400e; font-size: 14px; margin: 0; }" +
                ".footer { background: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb; }" +
                ".footer p { color: #9ca3af; font-size: 13px; margin: 5px 0; }" +
                "</style>" +
                "</head>" +
                "<body>" +
                "<div class='container'>" +
                "<div class='header'>" +
                "<div class='logo'>🔒</div>" +
                "<h1>Password Reset Request</h1>" +
                "</div>" +
                "<div class='content'>" +
                "<h2>Hello,</h2>" +
                "<p>We received a request to reset your ExportBridge account password.</p>" +
                "<p>To reset your password, use this verification code:</p>" +
                "<div class='token-container'>" +
                "<div class='token'>" + token + "</div>" +
                "</div>" +
                "<div class='warning'>" +
                "<span class='warning-icon'>⚠️</span>" +
                "<p class='warning-text'><strong>Important:</strong> This code will expire in 1 hour for security reasons.</p>" +
                "</div>" +
                "<p>If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>" +
                "</div>" +
                "<div class='footer'>" +
                "<p><strong>© 2024 ExportBridge</strong></p>" +
                "<p>International Export Documentation Management</p>" +
                "<p style='margin-top: 15px; font-size: 12px;'>This is an automated email. Please do not reply.</p>" +
                "</div>" +
                "</div>" +
                "</body>" +
                "</html>";
    }

    @Data
    public static class PasswordResetToken implements Serializable {
        @Serial
        private static final long serialVersionUID = 1L;

        private String token;
        private String email;
        private Long managerId;
        private LocalDateTime expiryTime;
    }
}