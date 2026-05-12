package GUI;

import Controllers.MarketController;
import Entities.Market;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.TextField;
import javafx.scene.layout.GridPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;
import javafx.scene.text.Text;

import java.io.IOException;
import java.util.List;
import java.util.stream.Collectors;

public class MarketsViewController {

    @FXML
    private Button tabAll;
    @FXML
    private Button tabWestern;
    @FXML
    private Button tabSouthern;
    @FXML
    private Button tabCentral;
    @FXML
    private TextField searchField;
    @FXML
    private Text resultCount;
    @FXML
    private GridPane marketsGrid;
    @FXML
    private VBox emptyState;

    private MarketController marketController;
    private List<Market> availableMarkets;
    private Button activeTab;
    private StackPane cachedContentArea;

    @FXML
    public void initialize() {
        marketController = new MarketController();
        activeTab = tabAll;
        loadMergedMarkets();
    }

    private void loadMergedMarkets() {
        System.out.println("=== LOADING MERGED MARKETS ===");

        availableMarkets = marketController.getMergedMarkets();

        System.out.println("✓ Found " + availableMarkets.size() + " markets");
        displayMarkets(availableMarkets);
    }

    private void displayMarkets(List<Market> markets) {
        marketsGrid.getChildren().clear();

        if (markets == null || markets.isEmpty()) {
            showEmptyState();
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
            if (column == 2) {
                column = 0;
                row++;
            }
        }

        resultCount.setText(markets.size() + " market" + (markets.size() != 1 ? "s" : ""));
    }

    private VBox createMarketCard(Market market) {
        String country = market.getName();

        VBox card = new VBox(16);
        card.getStyleClass().add("market-card");
        card.setPrefWidth(550);
        card.setAlignment(Pos.TOP_LEFT);
        card.setOnMouseClicked(e -> showCompaniesForCountry(market));
        card.setCursor(javafx.scene.Cursor.HAND);

        HBox header = new HBox(12);
        header.setAlignment(Pos.CENTER_LEFT);

        Text flag = new Text(getCountryFlag(market));
        flag.setStyle("-fx-font-size: 48px;");

        VBox countryInfo = new VBox(4);
        Text countryName = new Text(country);
        countryName.getStyleClass().add("market-name");

        Text region = new Text(market.getRegion() != null ? market.getRegion() : getRegion(country));
        region.getStyleClass().add("market-region");

        countryInfo.getChildren().addAll(countryName, region);
        header.getChildren().addAll(flag, countryInfo);

        HBox badgeRow = new HBox(8);
        if (market.isEu()) {
            Label euBadge = new Label("🇪🇺 EU Member");
            euBadge.getStyleClass().add("eu-badge");
            badgeRow.getChildren().add(euBadge);
        }

        int companyCount = marketController.getMergedCompanyCountForMarket(
                market.getCountryCode(),
                market.getName(),
                market.getRegion());
        Label companyBadge = new Label(companyCount + " Companies Available");
        companyBadge.getStyleClass().add("growth-badge");
        badgeRow.getChildren().add(companyBadge);

        HBox statsRow = new HBox(40);
        statsRow.setAlignment(Pos.CENTER_LEFT);
        statsRow.getStyleClass().add("market-stats");

        VBox companyBox = createStatBox("Companies", String.valueOf(companyCount));
        VBox potentialBox = createStatBox("Export Potential", "High");
        statsRow.getChildren().addAll(companyBox, potentialBox);

        HBox buttonRow = new HBox();
        buttonRow.setAlignment(Pos.CENTER_LEFT);

        Button companiesBtn = new Button("View Companies →");
        companiesBtn.getStyleClass().add("view-details-button");
        companiesBtn.setOnAction(e -> {
            e.consume();
            showCompaniesForCountry(market);
        });
        buttonRow.getChildren().add(companiesBtn);

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

    private void showCompaniesForCountry(Market market) {
        try {
            cachedContentArea = findContentArea();

            if (cachedContentArea == null) {
                System.err.println("✗ Cannot navigate - content area not found");
                return;
            }

            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/companies-browser-view.fxml"));
            Parent companiesView = loader.load();

            CompaniesBrowserViewController controller = loader.getController();
            controller.setMarket(market);
            controller.setContentArea(cachedContentArea);
            controller.setOnBackCallback(this::reloadMarketsViewInCachedArea);

            cachedContentArea.getChildren().clear();
            cachedContentArea.getChildren().add(companiesView);

            System.out.println("✓ Navigated to companies view for: " + market.getName());
        } catch (IOException e) {
            e.printStackTrace();
            showError("Failed to load companies view: " + e.getMessage());
        }
    }

    private void reloadMarketsViewInCachedArea() {
        if (cachedContentArea == null) {
            System.err.println("✗ Cached content area is null!");
            return;
        }

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/markets-view.fxml"));
            Parent marketsView = loader.load();

            cachedContentArea.getChildren().clear();
            cachedContentArea.getChildren().add(marketsView);

            System.out.println("✓ Markets view reloaded successfully");
        } catch (IOException e) {
            System.err.println("✗ Failed to reload markets view");
            e.printStackTrace();
        }
    }

    private StackPane findContentArea() {
        if (marketsGrid == null || marketsGrid.getScene() == null) {
            return null;
        }
        return (StackPane) marketsGrid.getScene().lookup("#contentArea");
    }

    @FXML
    private void filterAll() {
        setActiveTab(tabAll);
        displayMarkets(availableMarkets);
    }

    @FXML
    private void filterWestern() {
        setActiveTab(tabWestern);
        displayMarkets(availableMarkets.stream()
                .filter(market -> isWesternEurope(market.getName()))
                .collect(Collectors.toList()));
    }

    @FXML
    private void filterSouthern() {
        setActiveTab(tabSouthern);
        displayMarkets(availableMarkets.stream()
                .filter(market -> isSouthernEurope(market.getName()))
                .collect(Collectors.toList()));
    }

    @FXML
    private void filterCentral() {
        setActiveTab(tabCentral);
        displayMarkets(availableMarkets.stream()
                .filter(market -> isCentralEurope(market.getName()))
                .collect(Collectors.toList()));
    }

    @FXML
    private void handleSearch() {
        String query = searchField.getText().toLowerCase().trim();

        if (query.isEmpty()) {
            displayMarkets(availableMarkets);
            return;
        }

        List<Market> filtered = availableMarkets.stream()
                .filter(market -> containsIgnoreCase(market.getName(), query)
                        || containsIgnoreCase(market.getCountryCode(), query)
                        || containsIgnoreCase(market.getRegion(), query))
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

    private void showEmptyState() {
        marketsGrid.setManaged(false);
        marketsGrid.setVisible(false);
        emptyState.setManaged(true);
        emptyState.setVisible(true);
        resultCount.setText("0 markets");
    }

    private String getCountryFlag(Market market) {
        if (market == null) {
            return "🌍";
        }

        if (market.getCountryCode() != null && !market.getCountryCode().isBlank()) {
            return switch (market.getCountryCode().toUpperCase()) {
                case "FR" -> "🇫🇷";
                case "DE" -> "🇩🇪";
                case "IT" -> "🇮🇹";
                case "ES" -> "🇪🇸";
                case "BE" -> "🇧🇪";
                case "NL" -> "🇳🇱";
                case "PT" -> "🇵🇹";
                case "GR" -> "🇬🇷";
                case "AT" -> "🇦🇹";
                case "PL" -> "🇵🇱";
                case "GB" -> "🇬🇧";
                case "SE" -> "🇸🇪";
                case "DK" -> "🇩🇰";
                case "CN" -> "🇨🇳";
                case "JP" -> "🇯🇵";
                case "SG" -> "🇸🇬";
                case "TH" -> "🇹🇭";
                case "MY" -> "🇲🇾";
                case "ID" -> "🇮🇩";
                case "IN" -> "🇮🇳";
                case "TW" -> "🇹🇼";
                case "KR" -> "🇰🇷";
                case "US" -> "🇺🇸";
                case "ZA" -> "🇿🇦";
                case "AE" -> "🇦🇪";
                default -> "🌍";
            };
        }

        return getCountryFlag(market.getName());
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
            case "Austria" -> "🇦🇹";
            case "Poland" -> "🇵🇱";
            case "United Kingdom" -> "🇬🇧";
            case "Sweden" -> "🇸🇪";
            case "Denmark" -> "🇩🇰";
            case "China" -> "🇨🇳";
            case "Japan" -> "🇯🇵";
            case "Singapore" -> "🇸🇬";
            case "Thailand" -> "🇹🇭";
            case "Malaysia" -> "🇲🇾";
            case "Indonesia" -> "🇮🇩";
            case "India" -> "🇮🇳";
            case "Taiwan" -> "🇹🇼";
            case "South Korea" -> "🇰🇷";
            case "United States" -> "🇺🇸";
            case "South Africa" -> "🇿🇦";
            case "United Arab Emirates" -> "🇦🇪";
            default -> "🌍";
        };
    }

    private String getRegion(String country) {
        if (isWesternEurope(country))
            return "Western Europe";
        if (isSouthernEurope(country))
            return "Southern Europe";
        if (isCentralEurope(country))
            return "Central Europe";
        if (isAsian(country))
            return "Asia";
        if ("United States".equals(country))
            return "North America";
        if ("South Africa".equals(country))
            return "Africa";
        return "International";
    }

    private boolean isWesternEurope(String country) {
        return country != null && (country.equals("France") || country.equals("Belgium") ||
                country.equals("Netherlands") || country.equals("United Kingdom"));
    }

    private boolean isSouthernEurope(String country) {
        return country != null && (country.equals("Italy") || country.equals("Spain") ||
                country.equals("Portugal") || country.equals("Greece"));
    }

    private boolean isCentralEurope(String country) {
        return country != null && (country.equals("Germany") || country.equals("Austria") ||
                country.equals("Poland"));
    }

    private boolean isAsian(String country) {
        return country != null && (country.equals("China") || country.equals("Japan") ||
                country.equals("Singapore") || country.equals("Thailand") ||
                country.equals("Malaysia") || country.equals("Indonesia") ||
                country.equals("India") || country.equals("Taiwan") ||
                country.equals("South Korea"));
    }

    private boolean containsIgnoreCase(String value, String query) {
        return value != null && value.toLowerCase().contains(query);
    }

    private void showError(String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Error");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}