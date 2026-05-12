package GUI;

import Controllers.AuthenticationController;
import Controllers.CompanyController;
import Entities.Manager;
import Entities.Company;
import Utils.AppState;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;

public class SettingsViewController {

    @FXML private Button tabProfile;
    @FXML private Button tabCompany;
    @FXML private Button tabNotifications;
    @FXML private Button tabSecurity;
    @FXML private StackPane settingsContent;

    private AuthenticationController authController;
    private CompanyController companyController;
    private Button activeTab;

    @FXML
    public void initialize() {
        authController = new AuthenticationController();
        companyController = new CompanyController();
        activeTab = tabProfile;

        showProfile();
    }

    @FXML
    private void showProfile() {
        setActiveTab(tabProfile);
        settingsContent.getChildren().clear();
        settingsContent.getChildren().add(createProfileView());
    }

    @FXML
    private void showCompany() {
        setActiveTab(tabCompany);
        settingsContent.getChildren().clear();
        settingsContent.getChildren().add(createCompanyView());
    }

    @FXML
    private void showNotifications() {
        setActiveTab(tabNotifications);
        settingsContent.getChildren().clear();
        settingsContent.getChildren().add(createNotificationsView());
    }

    @FXML
    private void showSecurity() {
        setActiveTab(tabSecurity);
        settingsContent.getChildren().clear();
        settingsContent.getChildren().add(createSecurityView());
    }


    private void setActiveTab(Button tab) {
        if (activeTab != null) {
            activeTab.getStyleClass().remove("settings-tab-active");
        }
        tab.getStyleClass().add("settings-tab-active");
        activeTab = tab;
    }

    private ScrollPane createProfileView() {
        VBox content = new VBox(24);
        content.setPadding(new Insets(0));
        content.setMaxWidth(600);

        content.getChildren().add(createSectionTitle("Profile Information"));

        GridPane profileGrid = new GridPane();
        profileGrid.setHgap(16);
        profileGrid.setVgap(16);

        Manager manager = AppState.getCurrentManager();

        TextField firstNameField = new TextField(manager.getFirstName());
        TextField lastNameField = new TextField(manager.getLastName());
        TextField emailField = new TextField(manager.getEmail());
        emailField.setDisable(true); // Email cannot be changed
        TextField phoneField = new TextField("");
        phoneField.setPromptText("+216 12 345 678");

        addFormRow(profileGrid, 0, "First Name", firstNameField);
        addFormRow(profileGrid, 1, "Last Name", lastNameField);
        addFormRow(profileGrid, 2, "Email Address", emailField);
        addFormRow(profileGrid, 3, "Phone Number", phoneField);

        content.getChildren().add(profileGrid);

        VBox bioBox = new VBox(8);
        Label bioLabel = new Label("Bio");
        bioLabel.getStyleClass().add("form-label");

        TextArea bioArea = new TextArea();
        bioArea.setPromptText("Tell us about yourself and your experience...");
        bioArea.setPrefRowCount(4);
        bioArea.getStyleClass().add("form-field");

        bioBox.getChildren().addAll(bioLabel, bioArea);
        content.getChildren().add(bioBox);

        HBox buttonBox = new HBox(12);
        buttonBox.setAlignment(Pos.CENTER_LEFT);

        Button saveBtn = new Button("💾  Save Changes");
        saveBtn.getStyleClass().add("primary-button");
        saveBtn.setOnAction(e -> {
            // TODO: Implement save profile
            showSuccess("Profile updated successfully!");
        });

        buttonBox.getChildren().add(saveBtn);
        content.getChildren().add(buttonBox);

        ScrollPane scroll = new ScrollPane(content);
        scroll.setFitToWidth(true);
        scroll.getStyleClass().add("settings-scroll");

        return scroll;
    }

    private ScrollPane createCompanyView() {
        VBox content = new VBox(24);
        content.setPadding(new Insets(0));
        content.setMaxWidth(600);

        Manager manager = AppState.getCurrentManager();
        Company company = null;

        if (manager.getCompanyId() != null) {
            company = companyController.getCompany(manager.getCompanyId());
        }

        if (company == null) {
            // No company - show registration form
            content.getChildren().add(createSectionTitle("Register Your Company"));

            Text info = new Text("You haven't registered a company yet. Complete the form below to get started.");
            info.getStyleClass().add("info-text");
            content.getChildren().add(info);

            content.getChildren().add(createCompanyRegistrationForm());
        } else {
            // Company exists - show edit form
            content.getChildren().add(createSectionTitle("Company Information"));
            content.getChildren().add(createCompanyEditForm(company));
        }

        ScrollPane scroll = new ScrollPane(content);
        scroll.setFitToWidth(true);
        scroll.getStyleClass().add("settings-scroll");

        return scroll;
    }

    private VBox createCompanyRegistrationForm() {
        VBox form = new VBox(16);

        GridPane grid = new GridPane();
        grid.setHgap(16);
        grid.setVgap(16);

        TextField nameField = new TextField();
        nameField.setPromptText("Your Company Name");

        TextField taxField = new TextField();
        taxField.setPromptText("Tax Number / Matricule Fiscale");

        TextField regField = new TextField();
        regField.setPromptText("Registration Number");

        TextArea addressArea = new TextArea();
        addressArea.setPromptText("Company Address");
        addressArea.setPrefRowCount(2);
        addressArea.getStyleClass().add("form-field");

        TextField emailField = new TextField();
        emailField.setPromptText("contact@company.com");

        TextField phoneField = new TextField();
        phoneField.setPromptText("+216 12 345 678");

        addFormRow(grid, 0, "Company Name *", nameField);
        addFormRow(grid, 1, "Tax Number", taxField);
        addFormRow(grid, 2, "Registration Number", regField);
        addFormRow(grid, 3, "Contact Email", emailField);
        addFormRow(grid, 4, "Contact Phone", phoneField);

        form.getChildren().addAll(grid, addressArea);

        Button registerBtn = new Button("Register Company");
        registerBtn.getStyleClass().add("primary-button");
        registerBtn.setOnAction(e -> {
            if (nameField.getText().trim().isEmpty()) {
                showError("Company name is required");
                return;
            }

            Company newCompany = companyController.createCompany(
                    nameField.getText().trim(),
                    taxField.getText().trim(),
                    regField.getText().trim(),
                    addressArea.getText().trim(),
                    emailField.getText().trim(),
                    phoneField.getText().trim(),
                    AppState.getCurrentManager().getId()
            );

            if (newCompany != null) {
                showSuccess("Company registered successfully!");
                showCompany();
            } else {
                showError("Failed to register company");
            }
        });

        form.getChildren().add(registerBtn);

        return form;
    }

    private VBox createCompanyEditForm(Company company) {
        VBox form = new VBox(16);

        GridPane grid = new GridPane();
        grid.setHgap(16);
        grid.setVgap(16);

        TextField nameField = new TextField(company.getCompanyName());
        TextField taxField = new TextField(company.getTaxNumber());
        TextField regField = new TextField(company.getRegistrationNumber());
        TextArea addressArea = new TextArea(company.getAddress());
        addressArea.setPrefRowCount(2);
        addressArea.getStyleClass().add("form-field");
        TextField emailField = new TextField(company.getContactEmail());
        TextField phoneField = new TextField(company.getContactPhone());

        addFormRow(grid, 0, "Company Name", nameField);
        addFormRow(grid, 1, "Tax Number", taxField);
        addFormRow(grid, 2, "Registration Number", regField);
        addFormRow(grid, 3, "Contact Email", emailField);
        addFormRow(grid, 4, "Contact Phone", phoneField);

        form.getChildren().addAll(grid, addressArea);

        HBox statsBox = new HBox(40);
        statsBox.setPadding(new Insets(16, 0, 0, 0));
        statsBox.getStyleClass().add("stats-row");

        statsBox.getChildren().addAll(
                createStatBox("Rating", "⭐ " + (company.getRating() != null ? company.getRating() : "N/A")),
                createStatBox("Warnings", company.getWarnings() != null ? String.valueOf(company.getWarnings()) : "0"),
                createStatBox("Status", company.isBanned() ? "❌ Banned" : "✅ Active")
        );

        form.getChildren().add(statsBox);

        Button saveBtn = new Button("💾  Save Changes");
        saveBtn.getStyleClass().add("primary-button");
        saveBtn.setOnAction(e -> {
            company.setCompanyName(nameField.getText());
            company.setTaxNumber(taxField.getText());
            company.setRegistrationNumber(regField.getText());
            company.setAddress(addressArea.getText());
            company.setContactEmail(emailField.getText());
            company.setContactPhone(phoneField.getText());

            if (companyController.updateCompany(company)) {
                showSuccess("Company information updated!");
            } else {
                showError("Failed to update company");
            }
        });

        form.getChildren().add(saveBtn);

        return form;
    }

    private ScrollPane createNotificationsView() {
        VBox content = new VBox(24);
        content.setPadding(new Insets(0));
        content.setMaxWidth(600);

        content.getChildren().add(createSectionTitle("Notification Preferences"));

        VBox settingsBox = new VBox(16);

        CheckBox emailNotif = new CheckBox("Email notifications");
        emailNotif.setSelected(true);
        emailNotif.getStyleClass().add("settings-checkbox");

        CheckBox pushNotif = new CheckBox("Push notifications");
        pushNotif.setSelected(true);
        pushNotif.getStyleClass().add("settings-checkbox");

        CheckBox certExpiry = new CheckBox("Certificate expiry alerts");
        certExpiry.setSelected(true);
        certExpiry.getStyleClass().add("settings-checkbox");

        CheckBox partnerUpdates = new CheckBox("Partnership updates");
        partnerUpdates.setSelected(true);
        partnerUpdates.getStyleClass().add("settings-checkbox");

        CheckBox marketNews = new CheckBox("Market news and opportunities");
        marketNews.setSelected(false);
        marketNews.getStyleClass().add("settings-checkbox");

        settingsBox.getChildren().addAll(emailNotif, pushNotif, certExpiry, partnerUpdates, marketNews);

        content.getChildren().add(settingsBox);

        content.getChildren().add(createSectionTitle("Alert Timing"));

        HBox alertBox = new HBox(12);
        alertBox.setAlignment(Pos.CENTER_LEFT);

        Label alertLabel = new Label("Notify me");
        alertLabel.getStyleClass().add("form-label");

        ComboBox<String> daysCombo = new ComboBox<>();
        daysCombo.getItems().addAll("7 days", "15 days", "30 days", "60 days");
        daysCombo.setValue("30 days");
        daysCombo.getStyleClass().add("form-field");

        Label beforeLabel = new Label("before certificate expiry");
        beforeLabel.getStyleClass().add("form-label");

        alertBox.getChildren().addAll(alertLabel, daysCombo, beforeLabel);
        content.getChildren().add(alertBox);

        Button saveBtn = new Button("💾  Save Preferences");
        saveBtn.getStyleClass().add("primary-button");
        saveBtn.setOnAction(e -> showSuccess("Notification preferences saved!"));

        content.getChildren().add(saveBtn);

        ScrollPane scroll = new ScrollPane(content);
        scroll.setFitToWidth(true);
        scroll.getStyleClass().add("settings-scroll");

        return scroll;
    }

    private ScrollPane createSecurityView() {
        VBox content = new VBox(24);
        content.setPadding(new Insets(0));
        content.setMaxWidth(600);

        content.getChildren().add(createSectionTitle("Change Password"));

        GridPane passwordGrid = new GridPane();
        passwordGrid.setHgap(16);
        passwordGrid.setVgap(16);

        PasswordField currentPassword = new PasswordField();
        currentPassword.setPromptText("Current password");
        currentPassword.getStyleClass().add("form-field");

        PasswordField newPassword = new PasswordField();
        newPassword.setPromptText("New password");
        newPassword.getStyleClass().add("form-field");

        PasswordField confirmPassword = new PasswordField();
        confirmPassword.setPromptText("Confirm new password");
        confirmPassword.getStyleClass().add("form-field");

        addFormRow(passwordGrid, 0, "Current Password", currentPassword);
        addFormRow(passwordGrid, 1, "New Password", newPassword);
        addFormRow(passwordGrid, 2, "Confirm Password", confirmPassword);

        content.getChildren().add(passwordGrid);

        Label errorLabel = new Label();
        errorLabel.getStyleClass().add("error-label");
        errorLabel.setManaged(false);
        errorLabel.setVisible(false);
        content.getChildren().add(errorLabel);

        Button changeBtn = new Button("🔒  Change Password");
        changeBtn.getStyleClass().add("primary-button");
        changeBtn.setOnAction(e -> {
            if (currentPassword.getText().isEmpty() || newPassword.getText().isEmpty()) {
                errorLabel.setText("All fields are required");
                errorLabel.setManaged(true);
                errorLabel.setVisible(true);
                return;
            }

            if (!newPassword.getText().equals(confirmPassword.getText())) {
                errorLabel.setText("New passwords do not match");
                errorLabel.setManaged(true);
                errorLabel.setVisible(true);
                return;
            }

            boolean success = authController.changePassword(
                    currentPassword.getText(),
                    newPassword.getText(),
                    confirmPassword.getText()
            );

            if (success) {
                showSuccess("Password changed successfully!");
                currentPassword.clear();
                newPassword.clear();
                confirmPassword.clear();
                errorLabel.setManaged(false);
                errorLabel.setVisible(false);
            } else {
                errorLabel.setText("Failed to change password. Check your current password.");
                errorLabel.setManaged(true);
                errorLabel.setVisible(true);
            }
        });

        content.getChildren().add(changeBtn);

        content.getChildren().add(createSectionTitle("Session Information"));

        VBox sessionBox = new VBox(8);
        sessionBox.getStyleClass().add("info-box");

        Manager manager = AppState.getCurrentManager();

        Text emailText = new Text("Email: " + manager.getEmail());
        emailText.getStyleClass().add("info-text");

        Text lastLoginText = new Text("Last login: " +
                (manager.getLastLogin() != null ? manager.getLastLogin().toString() : "N/A"));
        lastLoginText.getStyleClass().add("info-text");

        sessionBox.getChildren().addAll(emailText, lastLoginText);
        content.getChildren().add(sessionBox);

        ScrollPane scroll = new ScrollPane(content);
        scroll.setFitToWidth(true);
        scroll.getStyleClass().add("settings-scroll");

        return scroll;
    }

    private VBox createSectionTitle(String title) {
        VBox box = new VBox();
        box.setPadding(new Insets(0, 0, 16, 0));

        Text text = new Text(title);
        text.getStyleClass().add("section-title");

        box.getChildren().add(text);
        return box;
    }

    private void addFormRow(GridPane grid, int row, String label, Control field) {
        Label labelNode = new Label(label);
        labelNode.getStyleClass().add("form-label");

        field.getStyleClass().add("form-field");
        GridPane.setHgrow(field, Priority.ALWAYS);

        grid.add(labelNode, 0, row);
        grid.add(field, 1, row);

        ColumnConstraints col1 = new ColumnConstraints();
        col1.setMinWidth(150);
        col1.setPrefWidth(150);

        ColumnConstraints col2 = new ColumnConstraints();
        col2.setHgrow(Priority.ALWAYS);

        grid.getColumnConstraints().setAll(col1, col2);
    }

    private VBox createStatBox(String label, String value) {
        VBox box = new VBox(4);

        Text labelText = new Text(label);
        labelText.getStyleClass().add("stat-label");

        Text valueText = new Text(value);
        valueText.getStyleClass().add("stat-value");

        box.getChildren().addAll(labelText, valueText);
        return box;
    }

    private void showSuccess(String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Success");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    private void showError(String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Error");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }

    // private void showInfo(String message) {
    //     Alert alert = new Alert(Alert.AlertType.INFORMATION);
    //     alert.setTitle("Info");
    //     alert.setHeaderText(null);
    //     alert.setContentText(message);
    //     alert.showAndWait();
    // }
}