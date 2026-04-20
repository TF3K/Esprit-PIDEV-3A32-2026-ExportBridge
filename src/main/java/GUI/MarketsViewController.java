package GUI;

import Controllers.MarketController;
import Controllers.CompanySeedController;
import Entities.Market;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Modality;
import javafx.stage.Stage;
import javafx.scene.Scene;

import java.io.IOException;
import java.util.List;
import java.util.Map;
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
    private CompanySeedController seedController;
    private List<String> availableCountries;
    private Map<String, Integer> companyCountByCountry;
    private Button activeTab;
    private StackPane cachedContentArea;

    @FXML
    public void initialize() {
        marketController = new MarketController();
        seedController = new CompanySeedController();
        activeTab = tabAll;

        loadMarketsFromCSV();
    }

    private void loadMarketsFromCSV() {
        System.out.println("=== LOADING MARKETS FROM CSV ===");

        availableCountries = seedController.getAvailableCountries();
        companyCountByCountry = seedController.getCompanyCountByCountry();

        System.out.println("✓ Found " + availableCountries.size() + " countries with companies");

        displayCountries(availableCountries);
    }

    private void displayCountries(List<String> countries) {
        marketsGrid.getChildren().clear();

        if (countries == null || countries.isEmpty()) {
            showEmptyState();
            return;
        }

        marketsGrid.setManaged(true);
        marketsGrid.setVisible(true);
        emptyState.setManaged(false);
        emptyState.setVisible(false);

        int column = 0;
        int row = 0;

        for (String country : countries) {
            VBox card = createCountryCard(country);
            marketsGrid.add(card, column, row);

            column++;
            if (column == 2) {
                column = 0;
                row++;
            }
        }

        resultCount.setText(countries.size() + " market" + (countries.size() != 1 ? "s" : ""));
    }

    private VBox createCountryCard(String country) {
        VBox card = new VBox(16);
        card.getStyleClass().add("market-card");
        card.setPrefWidth(550);
        card.setAlignment(Pos.TOP_LEFT);

        // Make card clickable
        card.setOnMouseClicked(e -> showCompaniesForCountry(country));
        card.setCursor(javafx.scene.Cursor.HAND);

        // Header with flag and country name
        HBox header = new HBox(12);
        header.setAlignment(Pos.CENTER_LEFT);

        // Flag emoji
        Text flag = new Text(getCountryFlag(country));
        flag.setStyle("-fx-font-size: 48px;");

        VBox countryInfo = new VBox(4);
        Text countryName = new Text(country);
        countryName.getStyleClass().add("market-name");

        Text region = new Text(getRegion(country));
        region.getStyleClass().add("market-region");

        countryInfo.getChildren().addAll(countryName, region);
        header.getChildren().addAll(flag, countryInfo);

        // Badges
        HBox badgeRow = new HBox(8);

        if (isEUCountry(country)) {
            Label euBadge = new Label("🇪🇺 EU Member");
            euBadge.getStyleClass().add("eu-badge");
            badgeRow.getChildren().add(euBadge);
        }

        // Company count badge
        int companyCount = companyCountByCountry.getOrDefault(country, 0);
        Label companyBadge = new Label(companyCount + " Companies Available");
        companyBadge.getStyleClass().add("growth-badge");
        badgeRow.getChildren().add(companyBadge);

        // Stats row
        HBox statsRow = new HBox(40);
        statsRow.setAlignment(Pos.CENTER_LEFT);
        statsRow.getStyleClass().add("market-stats");

        VBox companyBox = createStatBox("Companies", String.valueOf(companyCount));
        VBox potentialBox = createStatBox("Export Potential", "High");

        statsRow.getChildren().addAll(companyBox, potentialBox);

        // Button
        HBox buttonRow = new HBox();
        buttonRow.setAlignment(Pos.CENTER_LEFT);

        Button companiesBtn = new Button("View Companies →");
        companiesBtn.getStyleClass().add("view-details-button");
        companiesBtn.setOnAction(e -> {
            e.consume(); // Prevent card click
            showCompaniesForCountry(country);
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

    private void showCompaniesForCountry(String country) {
        try {
            cachedContentArea = findContentArea();

            if (cachedContentArea == null) {
                System.err.println("✗ Cannot navigate - content area not found");
                return;
            }

            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/companies-browser-view.fxml"));
            Parent companiesView = loader.load();

            CompaniesBrowserViewController controller = loader.getController();

            Market pseudoMarket = new Market();
            pseudoMarket.setName(country);
            pseudoMarket.setCountryCode(getCountryCode(country));

            controller.setMarket(pseudoMarket);
            controller.setContentArea(cachedContentArea);

            controller.setOnBackCallback(() -> {
                System.out.println("⚙ Back callback triggered");
                reloadMarketsViewInCachedArea();
            });

            cachedContentArea.getChildren().clear();
            cachedContentArea.getChildren().add(companiesView);
            System.out.println("✓ Navigated to companies view for: " + country);

        } catch (IOException e) {
            e.printStackTrace();
            showError("Failed to load companies view: " + e.getMessage());
        }
    }

    private void reloadMarketsViewInCachedArea() {
        System.out.println("⚙ Reloading markets view in cached content area");

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
        displayCountries(availableCountries);
    }

    @FXML
    private void filterWestern() {
        setActiveTab(tabWestern);
        List<String> filtered = availableCountries.stream()
                .filter(this::isWesternEurope)
                .collect(Collectors.toList());
        displayCountries(filtered);
    }

    @FXML
    private void filterSouthern() {
        setActiveTab(tabSouthern);
        List<String> filtered = availableCountries.stream()
                .filter(this::isSouthernEurope)
                .collect(Collectors.toList());
        displayCountries(filtered);
    }

    @FXML
    private void filterCentral() {
        setActiveTab(tabCentral);
        List<String> filtered = availableCountries.stream()
                .filter(this::isCentralEurope)
                .collect(Collectors.toList());
        displayCountries(filtered);
    }

    @FXML
    private void handleSearch() {
        String query = searchField.getText().toLowerCase().trim();

        if (query.isEmpty()) {
            displayCountries(availableCountries);
            return;
        }

        List<String> filtered = availableCountries.stream()
                .filter(c -> c.toLowerCase().contains(query))
                .collect(Collectors.toList());

        displayCountries(filtered);
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

    private String getCountryCode(String country) {
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
            case "United States" -> "US";
            case "South Africa" -> "ZA";
            case "United Arab Emirates" -> "AE";
            default -> "XX";
        };
    }

    private String getRegion(String country) {
        if (isWesternEurope(country)) return "Western Europe";
        if (isSouthernEurope(country)) return "Southern Europe";
        if (isCentralEurope(country)) return "Central Europe";
        if (isAsian(country)) return "Asia";
        if (country.equals("United States")) return "North America";
        if (country.equals("South Africa")) return "Africa";
        return "International";
    }

    private boolean isEUCountry(String country) {
        return country.equals("France") || country.equals("Germany") ||
                country.equals("Italy") || country.equals("Spain") ||
                country.equals("Belgium") || country.equals("Netherlands") ||
                country.equals("Portugal") || country.equals("Greece");
    }

    private boolean isWesternEurope(String country) {
        return country.equals("France") || country.equals("Belgium") ||
                country.equals("Netherlands") || country.equals("United Kingdom");
    }

    private boolean isSouthernEurope(String country) {
        return country.equals("Italy") || country.equals("Spain") ||
                country.equals("Portugal") || country.equals("Greece");
    }

    private boolean isCentralEurope(String country) {
        return country.equals("Germany") || country.equals("Austria") ||
                country.equals("Poland");
    }

    private boolean isAsian(String country) {
        return country.equals("China") || country.equals("Japan") ||
                country.equals("Singapore") || country.equals("Thailand") ||
                country.equals("Malaysia") || country.equals("Indonesia") ||
                country.equals("India") || country.equals("Taiwan") ||
                country.equals("South Korea");
    }

    private void showError(String message) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle("Error");
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}