package GUI;

import Controllers.ProductController;
import Entities.Product;
import Entities.ProductCategory;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.text.Text;
import javafx.stage.Stage;

public class ProductDialogController {

    @FXML private Text dialogTitle;
    @FXML private TextField nameField;
    @FXML private ComboBox<String> categoryField;
    @FXML private TextField hsCodeField;
    @FXML private TextArea descriptionField;
    @FXML private TextField quantityField;
    @FXML private TextField unitField;
    @FXML private TextField priceField;
    @FXML private ComboBox<String> currencyField;
    @FXML private Label errorLabel;

    private ProductController productController;
    private Product product;
    private Long companyId;
    private Runnable onSaveCallback;

    @FXML
    public void initialize() {
        productController = new ProductController();

        for (ProductCategory category : ProductCategory.values()) {
            categoryField.getItems().add(formatCategoryName(category.name()));
        }

        currencyField.getItems().addAll("TND", "EUR", "USD");
        currencyField.setValue("TND");
    }

    public void setProduct(Product product) {
        this.product = product;

        if (product != null) {
            dialogTitle.setText("Edit Product");
            populateFields();
        } else {
            dialogTitle.setText("Add Product");
        }
    }

    public void setCompanyId(Long companyId) {
        this.companyId = companyId;
    }

    public void setOnSaveCallback(Runnable callback) {
        this.onSaveCallback = callback;
    }

    private void populateFields() {
        nameField.setText(product.getName());
        categoryField.setValue(formatCategoryName(product.getCategory().name()));
        hsCodeField.setText(product.getHsCode());
        descriptionField.setText(product.getDescription());
        quantityField.setText(String.valueOf(product.getQuantity()));
        unitField.setText(product.getUnit());
        priceField.setText(String.valueOf(product.getUnitPrice()));
        currencyField.setValue(product.getCurrency());
    }

    @FXML
    private void handleSave() {
        if (!validateFields()) {
            return;
        }

        try {
            String name = nameField.getText().trim();
            String categoryStr = categoryField.getValue().toUpperCase().replace(" ", "_");
            ProductCategory category = ProductCategory.valueOf(categoryStr);
            String hsCode = hsCodeField.getText().trim();
            String description = descriptionField.getText().trim();
            Double quantity = Double.parseDouble(quantityField.getText().trim());
            String unit = unitField.getText().trim();
            Double price = Double.parseDouble(priceField.getText().trim());
            String currency = currencyField.getValue();

            boolean success;

            if (product == null) {
                // Create new product
                Product newProduct = productController.createProduct(
                        companyId, name, description, hsCode, category,
                        quantity, unit, price
                );
                success = newProduct != null;
            } else {
                product.setName(name);
                product.setCategory(category);
                product.setHsCode(hsCode);
                product.setDescription(description);
                product.setQuantity(quantity);
                product.setUnit(unit);
                product.setUnitPrice(price);
                product.setCurrency(currency);

                success = productController.updateProduct(product);
            }

            if (success) {
                if (onSaveCallback != null) {
                    onSaveCallback.run();
                }
                closeDialog();
            } else {
                showError("Failed to save product");
            }

        } catch (NumberFormatException e) {
            showError("Invalid number format in quantity or price");
        } catch (Exception e) {
            showError("Error saving product: " + e.getMessage());
        }
    }

    @FXML
    private void handleCancel() {
        closeDialog();
    }

    private boolean validateFields() {
        if (nameField.getText().trim().isEmpty()) {
            showError("Product name is required");
            return false;
        }

        if (categoryField.getValue() == null) {
            showError("Category is required");
            return false;
        }

        if (quantityField.getText().trim().isEmpty()) {
            showError("Quantity is required");
            return false;
        }

        if (priceField.getText().trim().isEmpty()) {
            showError("Price is required");
            return false;
        }

        try {
            Double.parseDouble(quantityField.getText().trim());
        } catch (NumberFormatException e) {
            showError("Quantity must be a valid number");
            return false;
        }

        try {
            Double.parseDouble(priceField.getText().trim());
        } catch (NumberFormatException e) {
            showError("Price must be a valid number");
            return false;
        }

        return true;
    }

    private void showError(String message) {
        errorLabel.setText(message);
        errorLabel.setVisible(true);
        errorLabel.setManaged(true);
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

    private void closeDialog() {
        Stage stage = (Stage) nameField.getScene().getWindow();
        stage.close();
    }
}