package GUI;

import Controllers.CompanySeedController;
import Entities.Company;
import Entities.Market;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import Utils.AppState;
import Entities.Manager;
import javafx.stage.Stage;

import java.io.IOException;
import java.util.List;
import java.util.stream.Collectors;

public class CompaniesBrowserViewController {

    @FXML private Button backButton;
    @FXML private Text flagText;
    @FXML private Text countryName;
    @FXML private Text companyCount;
    @FXML private TextField searchField;
    @FXML private Text resultCount;
    @FXML private VBox companiesList;
    @FXML private VBox emptyState;

    private CompanySeedController seedController;
    private Market currentMarket;
    private List<Company> allCompanies;
    private Runnable onBackCallback;
    private StackPane contentArea;

    @FXML
    public void initialize() {
        seedController = new CompanySeedController();
    }

    public void setMarket(Market market) {
        this.currentMarket = market;
        loadMarketCompanies();
    }

    public void setContentArea(StackPane contentArea) {
        this.contentArea = contentArea;
        System.out.println("✓ Content area set in CompaniesBrowserViewController");
    }

    public void setOnBackCallback(Runnable callback) {
        this.onBackCallback = callback;
    }

    private void loadMarketCompanies() {
        if (currentMarket == null) return;

        flagText.setText(getCountryFlag(currentMarket.getCountryCode()));
        countryName.setText(currentMarket.getName() + " Companies");

        allCompanies = seedController.getCompaniesForCountry(currentMarket.getName());

        if (allCompanies == null || allCompanies.isEmpty()) {
            showEmptyState();
            return;
        }

        companyCount.setText(allCompanies.size() + " companies available for partnerships");
        displayCompanies(allCompanies);
    }

    private void displayCompanies(List<Company> companies) {
        companiesList.getChildren().clear();

        if (companies == null || companies.isEmpty()) {
            showEmptyState();
            return;
        }

        companiesList.setManaged(true);
        companiesList.setVisible(true);
        emptyState.setManaged(false);
        emptyState.setVisible(false);

        for (Company company : companies) {
            HBox card = createCompanyCard(company);
            companiesList.getChildren().add(card);
        }

        resultCount.setText(companies.size() + " compan" + (companies.size() != 1 ? "ies" : "y"));
    }

    private HBox createCompanyCard(Company company) {
        HBox card = new HBox(20);
        card.getStyleClass().add("company-card");
        card.setAlignment(Pos.CENTER_LEFT);
        card.setPrefHeight(90);

        // Company icon based on industry
        StackPane iconBox = new StackPane();
        iconBox.getStyleClass().add("company-icon");
        iconBox.setPrefSize(60, 60);

        Text icon = new Text(getIndustryIcon(company.getDomain()));
        icon.setStyle("-fx-font-size: 28px;");
        iconBox.getChildren().add(icon);

        // Company info
        VBox infoBox = new VBox(6);
        HBox.setHgrow(infoBox, Priority.ALWAYS);

        Text name = new Text(company.getCompanyName());
        name.getStyleClass().add("company-name");

        // Industry badge (domain)
        if (company.getDomain() != null && !company.getDomain().isEmpty()) {
            Label industryBadge = new Label(company.getDomain());
            industryBadge.getStyleClass().add("industry-badge");
            infoBox.getChildren().add(industryBadge);
        }

        // Contact info row
        HBox contactRow = new HBox(20);

        if (company.getContactEmail() != null && !company.getContactEmail().isEmpty()) {
            Text email = new Text("✉️ " + company.getContactEmail());
            email.getStyleClass().add("company-contact");
            contactRow.getChildren().add(email);
        }

        if (company.getContactPhone() != null && !company.getContactPhone().isEmpty()) {
            Text phone = new Text("📞 " + company.getContactPhone());
            phone.getStyleClass().add("company-contact");
            contactRow.getChildren().add(phone);
        }

        if (!contactRow.getChildren().isEmpty()) {
            infoBox.getChildren().add(contactRow);
        }

        // Address if available
        if (company.getAddress() != null && !company.getAddress().isEmpty()) {
            Text address = new Text("📍 " + truncate(company.getAddress(), 80));
            address.getStyleClass().add("company-detail");
            infoBox.getChildren().add(address);
        }

        infoBox.getChildren().add(0, name);

        // Action buttons
        VBox actionBox = new VBox(8);
        actionBox.setAlignment(Pos.CENTER_RIGHT);
        actionBox.setPrefWidth(140);

        Button chatBtn = new Button("💬 Chat");
        chatBtn.getStyleClass().add("chat-button");
        chatBtn.setOnAction(e -> openChat(company));

        Button viewBtn = new Button("View Details");
        viewBtn.getStyleClass().add("view-button");
        viewBtn.setOnAction(e -> showCompanyDetails(company));

        Button partnerBtn = new Button("+ Add Partner");
        partnerBtn.getStyleClass().add("add-partner-button");
        partnerBtn.setOnAction(e -> handleAddPartner(company));

        actionBox.getChildren().addAll(viewBtn, partnerBtn, chatBtn);

        card.getChildren().addAll(iconBox, infoBox, actionBox);

        return card;
    }

    private void openChat(Company company) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/company-chat-view.fxml"));
            Parent chatView = loader.load();

            CompanyChatViewController controller = loader.getController();
            controller.setCompany(company);
            controller.setContentArea(contentArea);

            controller.setOnBackCallback(() -> {
                reloadCompaniesBrowser();
            });

            // Navigate to chat view
            contentArea.getChildren().clear();
            contentArea.getChildren().add(chatView);

            System.out.println("✓ Navigated to chat view for: " + company.getCompanyName());

        } catch (IOException e) {
            System.err.println("✗ Failed to open chat view");
            e.printStackTrace();
        }
    }

    private void reloadCompaniesBrowser() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/companies-browser-view.fxml"));
            Parent view = loader.load();

            CompaniesBrowserViewController controller = loader.getController();
            controller.setMarket(currentMarket);
            controller.setContentArea(contentArea);
            controller.setOnBackCallback(onBackCallback);

            contentArea.getChildren().clear();
            contentArea.getChildren().add(view);

            System.out.println("✓ Returned to companies browser");

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    /**
     * Get icon for industry
     */
    private String getIndustryIcon(String domain) {
        if (domain == null) return "🏢";

        switch (domain) {
            case "Food & Agriculture": return "🌾";
            case "Pharmaceuticals & Healthcare": return "💊";
            case "Technology & Electronics": return "💻";
            case "Textiles & Fashion": return "👔";
            case "Automotive & Transport": return "🚗";
            case "Construction & Engineering": return "🏗️";
            case "Energy & Resources": return "⚡";
            case "Chemicals & Materials": return "🧪";
            case "Import/Export & Trade": return "📦";
            case "Manufacturing": return "🏭";
            case "Retail & Distribution": return "🛒";
            case "Business Services": return "💼";
            default: return "🏢";
        }
    }

    private void showCompanyDetails(Company company) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Company Details");
        alert.setHeaderText(company.getCompanyName());

        StringBuilder details = new StringBuilder();
        details.append("Country: ").append(company.getCountry()).append("\n\n");

        if (company.getAddress() != null && !company.getAddress().isEmpty()) {
            details.append("Address:\n").append(company.getAddress()).append("\n\n");
        }

        if (company.getContactEmail() != null) {
            details.append("Email: ").append(company.getContactEmail()).append("\n");
        }

        if (company.getContactPhone() != null) {
            details.append("Phone: ").append(company.getContactPhone()).append("\n");
        }

        if (company.getDomain() != null) {
            details.append("Website: ").append(company.getDomain()).append("\n");
        }

        if (company.getTaxNumber() != null) {
            details.append("\nTax Number: ").append(company.getTaxNumber());
        }

        if (company.getRegistrationNumber() != null) {
            details.append("\nReg. Number: ").append(company.getRegistrationNumber());
        }

        alert.setContentText(details.toString());

        DialogPane dialogPane = alert.getDialogPane();
        dialogPane.setMinWidth(500);

        alert.showAndWait();
    }

    private void handleAddPartner(Company company) {
        Manager currentManager = AppState.getCurrentManager();

        if (currentManager == null || currentManager.getCompanyId() == null) {
            Alert alert = new Alert(Alert.AlertType.WARNING);
            alert.setTitle("Company Required");
            alert.setHeaderText("Register Your Company First");
            alert.setContentText("You need to register your company in Settings before creating partnerships.");
            alert.showAndWait();
            return;
        }

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/create-partnership-dialog.fxml"));
            Parent root = loader.load();

            CreatePartnershipDialogController controller = loader.getController();
            controller.setPartnerCompany(company);
            controller.setSourceCompanyId(currentManager.getCompanyId());
            controller.setOnSuccessCallback(() -> {
                showSuccess("Partnership request sent to " + company.getCompanyName());
            });

            Stage stage = new Stage();
            stage.setTitle("Create Partnership");
            stage.initModality(javafx.stage.Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root));
            stage.setResizable(false);
            stage.showAndWait();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Failed to open partnership dialog");
        }
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

    @FXML
    private void handleSearch() {
        String query = searchField.getText().toLowerCase().trim();

        if (query.isEmpty()) {
            displayCompanies(allCompanies);
            return;
        }

        List<Company> filtered = allCompanies.stream()
                .filter(c -> c.getCompanyName().toLowerCase().contains(query) ||
                        (c.getAddress() != null && c.getAddress().toLowerCase().contains(query)))
                .collect(Collectors.toList());

        displayCompanies(filtered);
    }

    @FXML
    private void handleBack() {
        System.out.println("⚙ Back button clicked");

        if (onBackCallback != null) {
            System.out.println("✓ Using callback");
            onBackCallback.run();
        } else {
            System.out.println("⚠ No callback, trying direct navigation");
            StackPane contentArea = findContentArea();

            if (contentArea != null) {
                try {
                    System.out.println("✓ Content area found, loading markets view");
                    FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/markets-view.fxml"));
                    Parent marketsView = loader.load();

                    contentArea.getChildren().clear();
                    contentArea.getChildren().add(marketsView);

                    System.out.println("✓ Markets view loaded");
                } catch (IOException e) {
                    System.err.println("✗ Failed to load markets view");
                    e.printStackTrace();
                }
            } else {
                System.err.println("✗ Content area not found!");
            }
        }
    }

    private StackPane findContentArea() {
        if (backButton == null) {
            System.err.println("✗ backButton is null");
            return null;
        }

        if (backButton.getScene() == null) {
            System.err.println("✗ Scene is null");
            return null;
        }

        System.out.println("⚙ Looking for #contentArea in scene");
        StackPane contentArea = (StackPane) backButton.getScene().lookup("#contentArea");

        if (contentArea == null) {
            System.err.println("✗ #contentArea not found in scene");

            // Debug: print all nodes with fx:id
            System.out.println("⚙ Searching for nodes with IDs...");
            backButton.getScene().getRoot().lookupAll("*").forEach(node -> {
                if (node.getId() != null) {
                    System.out.println("  Found node with ID: " + node.getId() + " (" + node.getClass().getSimpleName() + ")");
                }
            });
        } else {
            System.out.println("✓ Content area found: " + contentArea);
        }

        return contentArea;
    }

    private void showEmptyState() {
        companiesList.setManaged(false);
        companiesList.setVisible(false);
        emptyState.setManaged(true);
        emptyState.setVisible(true);
        companyCount.setText("No companies available");
        resultCount.setText("0 companies");
    }

    private String getCountryFlag(String countryCode) {
        switch (countryCode.toUpperCase()) {
            case "FR": return "🇫🇷";
            case "DE": return "🇩🇪";
            case "IT": return "🇮🇹";
            case "ES": return "🇪🇸";
            case "BE": return "🇧🇪";
            case "NL": return "🇳🇱";
            case "PT": return "🇵🇹";
            case "GR": return "🇬🇷";
            case "AT": return "🇦🇹";
            case "PL": return "🇵🇱";
            case "SE": return "🇸🇪";
            case "DK": return "🇩🇰";
            case "UK": case "GB": return "🇬🇧";
            default: return "🌍";
        }
    }

    private String getCompanyIcon(String companyName) {
        String name = companyName.toUpperCase();

        if (name.contains("FOOD") || name.contains("AGRI")) return "🌾";
        if (name.contains("TECH") || name.contains("DIGITAL")) return "💻";
        if (name.contains("EXPORT") || name.contains("TRADE")) return "📦";
        if (name.contains("PHARMA") || name.contains("MEDICAL")) return "💊";
        if (name.contains("FASHION") || name.contains("TEXTILE")) return "👔";
        if (name.contains("AUTO") || name.contains("MOTOR")) return "🚗";
        if (name.contains("CONSTRUCTION") || name.contains("BUILD")) return "🏗️";
        if (name.contains("ENERGY")) return "⚡";

        return "🏢"; // Default
    }

    private String truncate(String text, int maxLength) {
        if (text == null) return "";
        return text.length() > maxLength ? text.substring(0, maxLength) + "..." : text;
    }
}