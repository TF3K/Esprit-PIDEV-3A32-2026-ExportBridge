package GUI;

import Controllers.ProductController;
import Entities.Product;
import Entities.ProductCategory;
import Utils.AppState;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Modality;
import javafx.stage.Stage;

import java.io.IOException;
import java.util.List;
import java.util.stream.Collectors;

public class ProductsViewController {

    @FXML private TextField searchField;
    @FXML private ComboBox<String> categoryFilter;
    @FXML private Text resultCount;
    @FXML private GridPane productsGrid;
    @FXML private VBox emptyState;

    private ProductController productController;
    private List<Product> allProducts;
    private Long currentCompanyId;

    @FXML
    public void initialize() {
        productController = new ProductController();

        if (AppState.getCurrentManager() != null) {
            currentCompanyId = AppState.getCurrentManager().getCompanyId();
        }

        setupCategoryFilter();

        loadProducts();
    }

    private void setupCategoryFilter() {
        categoryFilter.getItems().add("All Categories");
        for (ProductCategory category : ProductCategory.values()) {
            categoryFilter.getItems().add(formatCategoryName(category.name()));
        }
        categoryFilter.setValue("All Categories");
    }

    private void loadProducts() {
        if (currentCompanyId == null) {
            showError("No company associated with your account");
            return;
        }

        allProducts = productController.getCompanyProducts(currentCompanyId);
        displayProducts(allProducts);
    }

    private void displayProducts(List<Product> products) {
        productsGrid.getChildren().clear();

        if (products == null || products.isEmpty()) {
            productsGrid.setManaged(false);
            productsGrid.setVisible(false);
            emptyState.setManaged(true);
            emptyState.setVisible(true);
            resultCount.setText("0 products");
            return;
        }

        productsGrid.setManaged(true);
        productsGrid.setVisible(true);
        emptyState.setManaged(false);
        emptyState.setVisible(false);

        int column = 0;
        int row = 0;

        for (Product product : products) {
            VBox card = createProductCard(product);
            productsGrid.add(card, column, row);

            column++;
            if (column == 3) {
                column = 0;
                row++;
            }
        }

        resultCount.setText(products.size() + " product" + (products.size() != 1 ? "s" : ""));
    }

    private VBox createProductCard(Product product) {
        VBox card = new VBox(12);
        card.getStyleClass().add("product-card");
        card.setPrefWidth(350);
        card.setAlignment(Pos.TOP_LEFT);

        // Product icon/image placeholder
        StackPane imagePlaceholder = new StackPane();
        imagePlaceholder.getStyleClass().add("product-image");
        imagePlaceholder.setPrefHeight(180);
        Text emoji = new Text(getCategoryEmoji(product.getCategory()));
        emoji.setStyle("-fx-font-size: 64px;");
        imagePlaceholder.getChildren().add(emoji);

        // Product name
        Text name = new Text(product.getName());
        name.getStyleClass().add("product-name");
        name.setWrappingWidth(330);

        // SKU
        if (product.getHsCode() != null && !product.getHsCode().isEmpty()) {
            Text sku = new Text("SKU: " + product.getHsCode());
            sku.getStyleClass().add("product-sku");
            card.getChildren().add(sku);
        }

        // Description
        if (product.getDescription() != null && !product.getDescription().isEmpty()) {
            Text description = new Text(product.getDescription());
            description.getStyleClass().add("product-description");
            description.setWrappingWidth(330);
            card.getChildren().add(description);
        }

        // Price and stock info
        HBox infoRow = new HBox(20);
        infoRow.setAlignment(Pos.CENTER_LEFT);

        VBox priceBox = new VBox(4);
        Text priceLabel = new Text("Price");
        priceLabel.getStyleClass().add("product-label");
        Text price = new Text(String.format("%.2f %s", product.getUnitPrice(), product.getCurrency()));
        price.getStyleClass().add("product-price");
        priceBox.getChildren().addAll(priceLabel, price);

        VBox stockBox = new VBox(4);
        Text stockLabel = new Text("Stock");
        stockLabel.getStyleClass().add("product-label");
        Text stock = new Text(String.format("%.0f %s", product.getQuantity(), product.getUnit()));
        stock.getStyleClass().add("product-stock");
        stockBox.getChildren().addAll(stockLabel, stock);

        infoRow.getChildren().addAll(priceBox, stockBox);

        // Category badge
        HBox categoryBox = new HBox();
        Label categoryBadge = new Label(formatCategoryName(product.getCategory().name()));
        categoryBadge.getStyleClass().add("category-badge");
        categoryBox.getChildren().add(categoryBadge);

        // Action buttons
        HBox buttonRow = new HBox(8);
        buttonRow.setAlignment(Pos.CENTER_LEFT);

        Button editBtn = new Button("Edit");
        editBtn.getStyleClass().add("edit-button");
        editBtn.setOnAction(e -> handleEditProduct(product));

        Button deleteBtn = new Button("Delete");
        deleteBtn.getStyleClass().add("delete-button");
        deleteBtn.setOnAction(e -> handleDeleteProduct(product));

        buttonRow.getChildren().addAll(editBtn, deleteBtn);

        card.getChildren().addAll(imagePlaceholder, name, infoRow, categoryBox, buttonRow);

        return card;
    }

    @FXML
    private void handleAddProduct() {
        showProductDialog(null);
    }

    private void handleEditProduct(Product product) {
        showProductDialog(product);
    }

    private void handleDeleteProduct(Product product) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Delete Product");
        alert.setHeaderText("Delete " + product.getName() + "?");
        alert.setContentText("This action cannot be undone.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                boolean success = productController.deleteProduct(product.getId());
                if (success) {
                    showSuccess("Product deleted successfully");
                    loadProducts();
                } else {
                    showError("Failed to delete product");
                }
            }
        });
    }

    private void showProductDialog(Product product) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/product-dialog.fxml"));
            Parent root = loader.load();

            ProductDialogController controller = loader.getController();
            controller.setProduct(product);
            controller.setCompanyId(currentCompanyId);
            controller.setOnSaveCallback(this::loadProducts);

            Stage stage = new Stage();
            stage.setTitle(product == null ? "Add Product" : "Edit Product");
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root));
            stage.setResizable(false);
            stage.showAndWait();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Failed to open product dialog");
        }
    }

    @FXML
    private void handleSearch() {
        String query = searchField.getText().toLowerCase().trim();

        if (query.isEmpty()) {
            displayProducts(allProducts);
            return;
        }

        List<Product> filtered = allProducts.stream()
                .filter(p -> p.getName().toLowerCase().contains(query) ||
                        (p.getHsCode() != null && p.getHsCode().toLowerCase().contains(query)) ||
                        (p.getDescription() != null && p.getDescription().toLowerCase().contains(query)))
                .collect(Collectors.toList());

        displayProducts(filtered);
    }

    @FXML
    private void handleCategoryFilter() {
        String selectedCategory = categoryFilter.getValue();

        if (selectedCategory == null || selectedCategory.equals("All Categories")) {
            displayProducts(allProducts);
            return;
        }

        String enumName = selectedCategory.toUpperCase().replace(" ", "_");

        List<Product> filtered = allProducts.stream()
                .filter(p -> p.getCategory().name().equals(enumName))
                .collect(Collectors.toList());

        displayProducts(filtered);
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

    private String getCategoryEmoji(ProductCategory category) {
        switch (category) {
            case OLIVE_OIL: return "🫒";
            case DATES: return "🌴";
            case TEXTILES: return "🧵";
            case ELECTRONICS: return "📱";
            case FOOD_BEVERAGE: return "🍽️";
            case SEAFOOD: return "🐟";
            case HANDICRAFTS: return "🎨";
            case MACHINERY: return "⚙️";
            case COSMETICS: return "💄";
            case CERAMICS: return "🏺";
            default: return "📦";
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
}