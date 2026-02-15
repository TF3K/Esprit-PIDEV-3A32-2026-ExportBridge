package GUI;

import Controllers.MarketController;
import Entities.Market;
import Entities.CertificateRequirement;
import Entities.ProductCategory;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Modality;
import javafx.stage.Stage;

import java.io.IOException;
import java.util.List;
import java.util.stream.Collectors;

public class MarketsViewController {

    @FXML private Button tabAll;
    @FXML private Button tabWestern;
    @FXML private Button tabSouthern;
    @FXML private Button tabCentral;
    @FXML private TextField searchField;
    @FXML private Text resultCount;
    @FXML private GridPane marketsGrid;
    @FXML private VBox emptyState;

    private MarketController marketController;
    private List<Market> allMarkets;
    private Button activeTab;

    @FXML
    public void initialize() {
        marketController = new MarketController();
        activeTab = tabAll;

        loadMarkets();
    }

    private void loadMarkets() {
        allMarkets = marketController.getAllMarkets();
        displayMarkets(allMarkets);
    }

    private void displayMarkets(List<Market> markets) {
        marketsGrid.getChildren().clear();

        if (markets == null || markets.isEmpty()) {
            marketsGrid.setManaged(false);
            marketsGrid.setVisible(false);
            emptyState.setManaged(true);
            emptyState.setVisible(true);
            resultCount.setText("0 markets");
            return;
        }

        marketsGrid.setManaged(true);
        marketsGrid.setVisible(true);
        emptyState.setManaged(false);
        emptyState.setVisible(false);

        int column = 0;
        int row = 0;

        for (Market market : markets) {
            VBox card = createMarketCard(market);
            marketsGrid.add(card, column, row);

            column++;
            if (column == 2) {  // 2 cards per row
                column = 0;
                row++;
            }
        }

        resultCount.setText(markets.size() + " market" + (markets.size() != 1 ? "s" : ""));
    }

    private VBox createMarketCard(Market market) {
        VBox card = new VBox(16);
        card.getStyleClass().add("market-card");
        card.setPrefWidth(550);
        card.setAlignment(Pos.TOP_LEFT);

        // Header with flag and country name
        HBox header = new HBox(12);
        header.setAlignment(Pos.CENTER_LEFT);

        // Flag emoji
        Text flag = new Text(getCountryFlag(market.getCountryCode()));
        flag.setStyle("-fx-font-size: 48px;");

        VBox countryInfo = new VBox(4);
        Text countryName = new Text(market.getName());
        countryName.getStyleClass().add("market-name");

        Text region = new Text(market.getRegion() != null ? market.getRegion() : "Europe");
        region.getStyleClass().add("market-region");

        countryInfo.getChildren().addAll(countryName, region);
        header.getChildren().addAll(flag, countryInfo);

        // Growth badge (if applicable)
        HBox badgeRow = new HBox(8);
        if (market.isEu()) {
            Label euBadge = new Label("🇪🇺 EU Member");
            euBadge.getStyleClass().add("eu-badge");
            badgeRow.getChildren().add(euBadge);
        }

        // Add growth indicator (you can customize this based on your data)
        Label growthBadge = new Label("+" + getRandomGrowth() + "% YoY Growth");
        growthBadge.getStyleClass().add("growth-badge");
        badgeRow.getChildren().add(growthBadge);

        // Description
        if (market.getDescription() != null && !market.getDescription().isEmpty()) {
            Text description = new Text(market.getDescription());
            description.getStyleClass().add("market-description");
            description.setWrappingWidth(520);
            card.getChildren().add(description);
        }

        // Stats row
        HBox statsRow = new HBox(40);
        statsRow.setAlignment(Pos.CENTER_LEFT);
        statsRow.getStyleClass().add("market-stats");

        VBox demandBox = createStatBox("Demand", getRandomDemand());
        VBox potentialBox = createStatBox("Export Potential", getRandomPotential());

        statsRow.getChildren().addAll(demandBox, potentialBox);

        // View Details Button
        HBox buttonRow = new HBox();
        buttonRow.setAlignment(Pos.CENTER_LEFT);

        Button detailsBtn = new Button("View Requirements →");
        detailsBtn.getStyleClass().add("view-details-button");
        detailsBtn.setOnAction(e -> showMarketDetails(market));

        buttonRow.getChildren().add(detailsBtn);

        card.getChildren().addAll(header, badgeRow, statsRow, buttonRow);

        return card;
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

    private void showMarketDetails(Market market) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/market-details-dialog.fxml"));
            Parent root = loader.load();

            MarketDetailsDialogController controller = loader.getController();
            controller.setMarket(market);

            Stage stage = new Stage();
            stage.setTitle(market.getName() + " - Market Details");
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root, 700, 600));
            stage.setResizable(false);
            stage.showAndWait();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Failed to open market details");
        }
    }

    @FXML
    private void filterAll() {
        setActiveTab(tabAll);
        displayMarkets(allMarkets);
    }

    @FXML
    private void filterWestern() {
        setActiveTab(tabWestern);
        List<Market> filtered = allMarkets.stream()
                .filter(m -> isWesternEurope(m.getCountryCode()))
                .collect(Collectors.toList());
        displayMarkets(filtered);
    }

    @FXML
    private void filterSouthern() {
        setActiveTab(tabSouthern);
        List<Market> filtered = allMarkets.stream()
                .filter(m -> isSouthernEurope(m.getCountryCode()))
                .collect(Collectors.toList());
        displayMarkets(filtered);
    }

    @FXML
    private void filterCentral() {
        setActiveTab(tabCentral);
        List<Market> filtered = allMarkets.stream()
                .filter(m -> isCentralEurope(m.getCountryCode()))
                .collect(Collectors.toList());
        displayMarkets(filtered);
    }

    @FXML
    private void handleSearch() {
        String query = searchField.getText().toLowerCase().trim();

        if (query.isEmpty()) {
            displayMarkets(allMarkets);
            return;
        }

        List<Market> filtered = allMarkets.stream()
                .filter(m -> m.getName().toLowerCase().contains(query) ||
                        m.getCountryCode().toLowerCase().contains(query) ||
                        (m.getDescription() != null && m.getDescription().toLowerCase().contains(query)))
                .collect(Collectors.toList());

        displayMarkets(filtered);
    }

    private void setActiveTab(Button tab) {
        if (activeTab != null) {
            activeTab.getStyleClass().remove("filter-tab-active");
        }
        tab.getStyleClass().add("filter-tab-active");
        activeTab = tab;
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
            default: return "🇪🇺";
        }
    }

    private boolean isWesternEurope(String code) {
        return code.matches("FR|BE|NL|LU");
    }

    private boolean isSouthernEurope(String code) {
        return code.matches("IT|ES|PT|GR");
    }

    private boolean isCentralEurope(String code) {
        return code.matches("DE|AT|PL|CZ|HU");
    }

    private String getRandomGrowth() {
        String[] growths = {"15", "18", "20", "22", "24", "28"};
        return growths[(int)(Math.random() * growths.length)];
    }

    private String getRandomDemand() {
        String[] demands = {"Very High", "High", "Medium"};
        return demands[(int)(Math.random() * demands.length)];
    }

    private String getRandomPotential() {
        String[] potentials = {"€1.2M", "€950K", "€780K", "€650K", "€1.5M"};
        return potentials[(int)(Math.random() * potentials.length)];
    }

    private void showError(String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Error");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}