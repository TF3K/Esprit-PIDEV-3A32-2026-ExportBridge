package GUI;

import Controllers.AuthenticationController;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.VBox;
import javafx.stage.Stage;

public class ForgotPasswordDialogController {

    @FXML private VBox emailStep;
    @FXML private VBox resetStep;
    @FXML private VBox successStep;

    @FXML private TextField emailField;
    @FXML private TextField tokenField;
    @FXML private PasswordField newPasswordField;
    @FXML private PasswordField confirmPasswordField;

    @FXML private Label emailErrorLabel;
    @FXML private Label resetErrorLabel;
    @FXML private Button cancelButton;

    private AuthenticationController authController;

    @FXML
    public void initialize() {
        authController = new AuthenticationController();
    }

    @FXML
    private void handleSendCode() {
        String email = emailField.getText().trim();

        if (email.isEmpty()) {
            showEmailError("Please enter your email address");
            return;
        }

        if (!isValidEmail(email)) {
            showEmailError("Please enter a valid email address");
            return;
        }

        Button sendButton = (Button) emailStep.lookup(".primary-button");
        sendButton.setDisable(true);
        sendButton.setText("Sending...");

        new Thread(() -> {
            boolean success = authController.requestPasswordReset(email);

            Platform.runLater(() -> {
                if (success) {
                    showEmailSuccess("Verification code sent! Check your email.");

                    new Thread(() -> {
                        try {
                            Thread.sleep(2000);
                            Platform.runLater(this::showResetStep);
                        } catch (InterruptedException e) {
                            e.printStackTrace();
                        }
                    }).start();
                } else {
                    showEmailError("Failed to send email. Please try again.");
                    sendButton.setDisable(false);
                    sendButton.setText("Send Verification Code");
                }
            });
        }).start();
    }

    @FXML
    private void handleResetPassword() {
        String token = tokenField.getText().trim();
        String newPassword = newPasswordField.getText();
        String confirmPassword = confirmPasswordField.getText();

        if (token.isEmpty()) {
            showResetError("Please enter the verification code");
            return;
        }

        if (newPassword.isEmpty()) {
            showResetError("Please enter a new password");
            return;
        }

        if (newPassword.length() < 8) {
            showResetError("Password must be at least 8 characters");
            return;
        }

        if (!newPassword.equals(confirmPassword)) {
            showResetError("Passwords do not match");
            return;
        }

        Button resetButton = (Button) resetStep.lookup(".primary-button");
        resetButton.setDisable(true);
        resetButton.setText("Resetting...");

        boolean success = authController.resetPassword(token, newPassword, confirmPassword);

        if (success) {
            showSuccessStep();
        } else {
            showResetError("Invalid or expired verification code. Please try again.");
            resetButton.setDisable(false);
            resetButton.setText("Reset Password");
        }
    }

    @FXML
    private void handleClose() {
        Stage stage = (Stage) emailField.getScene().getWindow();
        stage.close();
    }

    private void showResetStep() {
        emailStep.setManaged(false);
        emailStep.setVisible(false);
        resetStep.setManaged(true);
        resetStep.setVisible(true);
    }

    private void showSuccessStep() {
        resetStep.setManaged(false);
        resetStep.setVisible(false);
        successStep.setManaged(true);
        successStep.setVisible(true);
        cancelButton.setManaged(false);
        cancelButton.setVisible(false);
    }

    private void showEmailError(String message) {
        emailErrorLabel.setText(message);
        emailErrorLabel.setStyle("-fx-text-fill: #ef4444;");
        emailErrorLabel.setManaged(true);
        emailErrorLabel.setVisible(true);
    }

    private void showEmailSuccess(String message) {
        emailErrorLabel.setText(message);
        emailErrorLabel.setStyle("-fx-text-fill: #22c55e;");
        emailErrorLabel.setManaged(true);
        emailErrorLabel.setVisible(true);
    }

    private void showResetError(String message) {
        resetErrorLabel.setText(message);
        resetErrorLabel.setManaged(true);
        resetErrorLabel.setVisible(true);
    }

    private boolean isValidEmail(String email) {
        return email.matches("^[A-Za-z0-9+_.-]+@(.+)$");
    }
}