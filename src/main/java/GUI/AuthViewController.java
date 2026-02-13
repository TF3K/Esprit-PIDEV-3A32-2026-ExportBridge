package GUI;

import Controllers.AuthenticationController;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.layout.HBox;
import javafx.scene.layout.VBox;
import javafx.scene.text.Text;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.io.IOException;

public class AuthViewController {

    @FXML private Button loginTabButton;
    @FXML private Button signupTabButton;

    @FXML private VBox formContainer;
    @FXML private VBox firstNameField;
    @FXML private TextField firstNameInput;
    @FXML private VBox lastNameField;
    @FXML private TextField lastNameInput;
    @FXML private TextField emailInput;
    @FXML private PasswordField passwordInput;
    @FXML private VBox confirmPasswordField;
    @FXML private PasswordField confirmPasswordInput;

    @FXML private HBox loginOptions;
    @FXML private CheckBox rememberMeCheck;

    @FXML private Button submitButton;
    @FXML private Label errorLabel;
    @FXML private Text footerText;
    @FXML private Hyperlink footerLink;

    private AuthenticationController authController;
    private boolean isLoginMode = true;

    @FXML
    public void initialize() {
        authController = new AuthenticationController();

        emailInput.requestFocus();

        updateUIForMode();

        emailInput.setOnAction(e -> passwordInput.requestFocus());
        passwordInput.setOnAction(e -> {
            if (isLoginMode) {
                handleSubmit();
            } else {
                confirmPasswordInput.requestFocus();
            }
        });
        confirmPasswordInput.setOnAction(e -> handleSubmit());
    }

    @FXML
    private void switchToLogin() {
        if (!isLoginMode) {
            isLoginMode = true;
            updateUIForMode();
            clearForm();
            resizeWindow(-100);
        }
    }

    @FXML
    private void switchToSignup() {
        if (isLoginMode) {
            isLoginMode = false;
            updateUIForMode();
            clearForm();
            resizeWindow(100);
        }
    }

    @FXML
    private void toggleAuthMode() {
        if (isLoginMode) {
            switchToSignup();
        } else {
            switchToLogin();
        }
    }

    @FXML
    private void handleSubmit() {
        hideError();

        String email = emailInput.getText().trim();
        String password = passwordInput.getText();

        if (email.isEmpty() || password.isEmpty()) {
            showError("Email and password are required");
            return;
        }

        submitButton.setDisable(true);

        if (isLoginMode) {
            handleLogin(email, password);
        } else {
            handleSignup(email, password);
        }

        submitButton.setDisable(false);
    }

    private void handleLogin(String email, String password) {
        String token = authController.login(email, password);

        if (token != null) {
            System.out.println("Login successful!");
            showSuccess("Login successful!");

            new Thread(() -> {
                try {
                    Thread.sleep(2000);
                    Platform.runLater(this::closeApplication);
                } catch (InterruptedException e) {
                    System.err.println("Thread interrupted: " + e.getMessage());
                }
            }).start();
        } else {
            showError("Invalid email or password");
        }
    }

    private void handleSignup(String email, String password) {
        String firstName = firstNameInput.getText().trim();
        String lastName = lastNameInput.getText().trim();
        String confirmPassword = confirmPasswordInput.getText();

        if (firstName.isEmpty()) {
            showError("First name is required");
            return;
        }

        if (lastName.isEmpty()) {
            showError("Last name is required");
            return;
        }

        if (password.length() < 8) {
            showError("Password must be at least 8 characters");
            return;
        }

        if (!password.equals(confirmPassword)) {
            showError("Passwords do not match");
            return;
        }

        boolean success = authController.register(
                firstName,
                lastName,
                email,
                password,
                confirmPassword,
                null
        );

        if (success) {
            showSuccess("Account created successfully! Please login.");
            new Thread(() -> {
                try {
                    Thread.sleep(2000);
                    Platform.runLater(() -> {
                        switchToLogin();
                        emailInput.setText(email);  // Pre-fill email
                        passwordInput.requestFocus();
                    });
                } catch (InterruptedException e) {
                    System.err.println("Thread interrupted: " + e.getMessage());
                }
            }).start();
        } else {
            showError("Registration failed. Email may already be in use.");
        }
    }

    @FXML
    private void handleForgotPassword() {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Forgot Password");
        alert.setHeaderText(null);
        alert.setContentText("Password recovery feature coming soon!");
        alert.showAndWait();
    }

    private void updateUIForMode() {
        if (isLoginMode) {
            if (!loginTabButton.getStyleClass().contains("tab-active")) {
                loginTabButton.getStyleClass().add("tab-active");
            }
            signupTabButton.getStyleClass().remove("tab-active");

            firstNameField.setVisible(false);
            firstNameField.setManaged(false);
            lastNameField.setVisible(false);
            lastNameField.setManaged(false);
            confirmPasswordField.setVisible(false);
            confirmPasswordField.setManaged(false);
            loginOptions.setVisible(true);
            loginOptions.setManaged(true);

            submitButton.setText("Login");
            footerText.setText("Don't have an account?");
            footerLink.setText("Sign up");

        } else {
            if (!signupTabButton.getStyleClass().contains("tab-active")) {
                signupTabButton.getStyleClass().add("tab-active");
            }
            loginTabButton.getStyleClass().remove("tab-active");

            firstNameField.setVisible(true);
            firstNameField.setManaged(true);
            lastNameField.setVisible(true);
            lastNameField.setManaged(true);
            confirmPasswordField.setVisible(true);
            confirmPasswordField.setManaged(true);
            loginOptions.setVisible(false);
            loginOptions.setManaged(false);

            submitButton.setText("Create Account");
            footerText.setText("Already have an account?");
            footerLink.setText("Login");
        }
    }

    private void clearForm() {
        firstNameInput.clear();
        lastNameInput.clear();
        emailInput.clear();
        passwordInput.clear();
        confirmPasswordInput.clear();
        rememberMeCheck.setSelected(false);
        hideError();
    }

    private void showError(String message) {
        errorLabel.setText(message);
        errorLabel.setStyle("-fx-text-fill: #ef4444;");
        errorLabel.setVisible(true);
        errorLabel.setManaged(true);
    }

    private void showSuccess(String message) {
        errorLabel.setText(message);
        errorLabel.setStyle("-fx-text-fill: #22c55e;");
        errorLabel.setVisible(true);
        errorLabel.setManaged(true);
    }

    private void hideError() {
        errorLabel.setVisible(false);
        errorLabel.setManaged(false);
    }

    private void closeApplication() {
        Stage stage = (Stage) submitButton.getScene().getWindow();

        System.out.println("Closing application...");
        System.out.println("User: " + authController.getCurrentManager().getEmail());

        stage.close();

        javafx.application.Platform.exit();
    }

    private void resizeWindow(double heightChange) {
        if (submitButton.getScene() != null) {
            Stage stage = (Stage) submitButton.getScene().getWindow();
            if (stage != null) {
                double verticalShift = heightChange / 2;

                stage.setY(stage.getY() - verticalShift);

                stage.setHeight(stage.getHeight() + heightChange);
            }
        }
    }
}