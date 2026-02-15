package GUI;

import Controllers.MarketController;
import Entities.Market;
import Entities.CertificateRequirement;
import Entities.ProductCategory;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Stage;

import java.util.List;

public class MarketDetailsDialogController {

    @FXML private Text flagText;
    @FXML private Text marketName;
    @FXML private Text regionText;
    @FXML private VBox tradeAgreementBox;
    @FXML private Text tradeAgreementText;
    @FXML private VBox descriptionBox;
    @FXML private Text descriptionText;
    @FXML private ComboBox<String> categorySelector;
    @FXML private VBox requirementsList;

    private MarketController marketController;
    private Market market;

    @FXML
    public void initialize() {
        marketController = new MarketController();

        // Setup category selector
        for (ProductCategory category : ProductCategory.values()) {
            categorySelector.getItems().add(formatCategoryName(category.name()));
        }
    }

    public void setMarket(Market market) {
        this.market = market;
        populateMarketInfo();
    }

    private void populateMarketInfo() {
        // Set flag
        flagText.setText(getCountryFlag(market.getCountryCode()));

        // Set name and region
        marketName.setText(market.getName());
        regionText.setText(market.getRegion() != null ? market.getRegion() : "Europe");

        // Set trade agreement if exists
        if (market.getTradeAgreement() != null && !market.getTradeAgreement().isEmpty()) {
            tradeAgreementText.setText(market.getTradeAgreement());
            tradeAgreementBox.setManaged(true);
            tradeAgreementBox.setVisible(true);
        }

        // Set description if exists
        if (market.getDescription() != null && !market.getDescription().isEmpty()) {
            descriptionText.setText(market.getDescription());
            descriptionBox.setManaged(true);
            descriptionBox.setVisible(true);
        }

        // Load all requirements initially
        loadAllRequirements();
    }

    private void loadAllRequirements() {
        requirementsList.getChildren().clear();

        List<CertificateRequirement> requirements = marketController.getMarketRequirements(market.getId());

        if (requirements == null || requirements.isEmpty()) {
            Text noReqs = new Text("No specific certificate requirements available for this market.");
            noReqs.setStyle("-fx-fill: #6b7280; -fx-font-size: 14px;");
            requirementsList.getChildren().add(noReqs);
            return;
        }

        // Group by category
        ProductCategory currentCategory = null;

        for (CertificateRequirement req : requirements) {
            // Add category header if changed
            if (currentCategory != req.getProductCategory()) {
                currentCategory = req.getProductCategory();

                Text categoryHeader = new Text(formatCategoryName(currentCategory.name()));
                categoryHeader.setStyle("-fx-font-size: 16px; -fx-font-weight: 600; -fx-fill: #1a1d29;");
                VBox.setMargin(categoryHeader, new Insets(12, 0, 8, 0));
                requirementsList.getChildren().add(categoryHeader);
            }

            // Add requirement card
            requirementsList.getChildren().add(createRequirementCard(req));
        }
    }

    @FXML
    private void handleCategoryChange() {
        String selectedCategory = categorySelector.getValue();

        if (selectedCategory == null) {
            loadAllRequirements();
            return;
        }

        requirementsList.getChildren().clear();

        // Convert display name to enum
        String enumName = selectedCategory.toUpperCase().replace(" ", "_");
        ProductCategory category = ProductCategory.valueOf(enumName);

        List<CertificateRequirement> requirements =
                marketController.getRequirementsForProduct(market.getId(), category);

        if (requirements == null || requirements.isEmpty()) {
            Text noReqs = new Text("No certificate requirements for " + selectedCategory);
            noReqs.setStyle("-fx-fill: #6b7280; -fx-font-size: 14px;");
            requirementsList.getChildren().add(noReqs);
            return;
        }

        for (CertificateRequirement req : requirements) {
            requirementsList.getChildren().add(createRequirementCard(req));
        }
    }

    private HBox createRequirementCard(CertificateRequirement req) {
        HBox card = new HBox(12);
        card.getStyleClass().add("requirement-card");
        card.setAlignment(javafx.geometry.Pos.CENTER_LEFT);

        // Mandatory indicator
        StackPane indicator = new StackPane();
        indicator.setPrefSize(8, 8);
        indicator.setStyle(req.isMandatory()
                ? "-fx-background-color: #ef4444; -fx-background-radius: 4px;"
                : "-fx-background-color: #10b981; -fx-background-radius: 4px;");

        // Content
        VBox content = new VBox(4);
        HBox.setHgrow(content, javafx.scene.layout.Priority.ALWAYS);

        Text certName = new Text(formatCertificateName(req.getCertificateType().name()));
        certName.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-fill: #1a1d29;");

        Text status = new Text(req.isMandatory() ? "REQUIRED" : "Optional");
        status.setStyle(req.isMandatory()
                ? "-fx-font-size: 12px; -fx-fill: #ef4444; -fx-font-weight: 600;"
                : "-fx-font-size: 12px; -fx-fill: #10b981; -fx-font-weight: 600;");

        content.getChildren().addAll(certName, status);

        if (req.getDescription() != null && !req.getDescription().isEmpty()) {
            Text desc = new Text(req.getDescription());
            desc.setStyle("-fx-font-size: 13px; -fx-fill: #6b7280;");
            desc.setWrappingWidth(550);
            content.getChildren().add(desc);
        }

        card.getChildren().addAll(indicator, content);

        return card;
    }

    @FXML
    private void handleClose() {
        Stage stage = (Stage) marketName.getScene().getWindow();
        stage.close();
    }

    private String formatCategoryName(String category) {
        String[] words = category.replace("_", " ").toLowerCase().split(" ");
        StringBuilder formatted = new StringBuilder();

        for (String word : words) {
            if (!word.isEmpty()) {
                formatted.append(Character.toUpperCase(word.charAt(0)))
                        .append(word.substring(1))
                        .append(" ");
            }
        }

        return formatted.toString().trim();
    }

    private String formatCertificateName(String certType) {
        return certType.replace("_", " ");
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
            default: return "🇪🇺";
        }
    }
}