package GUI;

import Controllers.ProductController;
import Entities.Product;
import Entities.ProductCategory;
import Services.EmailService;
import Utils.AppState;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.WriterException;
import com.google.zxing.common.BitMatrix;
import com.google.zxing.qrcode.QRCodeWriter;

import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.kernel.pdf.PdfReader;
import com.itextpdf.kernel.colors.ColorConstants;
import com.itextpdf.kernel.colors.DeviceRgb;
import com.itextpdf.kernel.font.PdfFontFactory;
import com.itextpdf.kernel.font.PdfFont;
import com.itextpdf.io.font.constants.StandardFonts;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Table;
import com.itextpdf.layout.properties.TextAlignment;
import com.itextpdf.layout.properties.UnitValue;
import com.itextpdf.kernel.pdf.canvas.parser.PdfTextExtractor;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.ButtonType;
import javafx.scene.control.ComboBox;
import javafx.scene.control.Label;
import javafx.scene.control.TextArea;
import javafx.scene.control.TextField;
import javafx.scene.image.ImageView;
import javafx.scene.image.PixelWriter;
import javafx.scene.image.WritableImage;
import javafx.scene.layout.HBox;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;
import javafx.scene.layout.GridPane;
import javafx.scene.text.Text;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.paint.Color;
import javafx.stage.FileChooser;
import javafx.stage.Modality;
import javafx.stage.Stage;

import javax.imageio.ImageIO;
import java.awt.Desktop;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.net.URI;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

public class ProductsViewController {

    @FXML
    private TextField searchField;
    @FXML
    private ComboBox<String> categoryFilter;
    @FXML
    private Text resultCount;
    @FXML
    private GridPane productsGrid;
    @FXML
    private VBox emptyState;

    @FXML
    private Text statTotalProducts;
    @FXML
    private Text statCategories;
    @FXML
    private Text statTotalValue;
    @FXML
    private Text statAvgPrice;
    @FXML
    private Text statTotalStock;

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
        updateStatistics();
        displayProducts(allProducts);
    }

    private void updateStatistics() {
        if (allProducts == null || allProducts.isEmpty()) {
            statTotalProducts.setText("0");
            statCategories.setText("0");
            statTotalValue.setText("0");
            statAvgPrice.setText("0");
            statTotalStock.setText("0");
            return;
        }

        statTotalProducts.setText(String.valueOf(allProducts.size()));

        long catCount = allProducts.stream()
                .map(Product::getCategory)
                .distinct()
                .count();
        statCategories.setText(String.valueOf(catCount));

        double totalValue = allProducts.stream()
                .mapToDouble(p -> p.getTotalValue() != null ? p.getTotalValue() : 0)
                .sum();
        statTotalValue.setText(formatCurrency(totalValue));

        double avgPrice = allProducts.stream()
                .mapToDouble(p -> p.getUnitPrice() != null ? p.getUnitPrice() : 0)
                .average()
                .orElse(0);
        statAvgPrice.setText(formatCurrency(avgPrice));

        double totalStock = allProducts.stream()
                .mapToDouble(p -> p.getQuantity() != null ? p.getQuantity() : 0)
                .sum();
        statTotalStock.setText(String.format("%.0f", totalStock));
    }

    private String formatCurrency(double value) {
        if (value >= 1_000_000) {
            return String.format("%.1fM", value / 1_000_000);
        } else if (value >= 1_000) {
            return String.format("%.1fK", value / 1_000);
        }
        return String.format("%.0f", value);
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

        StackPane imagePlaceholder = new StackPane();
        imagePlaceholder.getStyleClass().add("product-image");
        imagePlaceholder.setPrefHeight(180);
        Text emoji = new Text(getCategoryEmoji(product.getCategory()));
        emoji.setStyle("-fx-font-size: 64px;");
        imagePlaceholder.getChildren().add(emoji);

        Text name = new Text(product.getName());
        name.getStyleClass().add("product-name");
        name.setWrappingWidth(330);

        card.getChildren().addAll(imagePlaceholder, name);

        if (product.getHsCode() != null && !product.getHsCode().isEmpty()) {
            Text sku = new Text("SKU: " + product.getHsCode());
            sku.getStyleClass().add("product-sku");
            card.getChildren().add(sku);
        }

        if (product.getDescription() != null && !product.getDescription().isEmpty()) {
            Text description = new Text(product.getDescription());
            description.getStyleClass().add("product-description");
            description.setWrappingWidth(330);
            card.getChildren().add(description);
        }

        HBox infoRow = new HBox(20);
        infoRow.setAlignment(Pos.CENTER_LEFT);

        VBox priceBox = new VBox(4);
        Text priceLabel = new Text("Price");
        priceLabel.getStyleClass().add("product-label");
        Text price = new Text(String.format("%.2f %s",
                product.getUnitPrice() != null ? product.getUnitPrice() : 0,
                product.getCurrency() != null ? product.getCurrency() : "TND"));
        price.getStyleClass().add("product-price");
        priceBox.getChildren().addAll(priceLabel, price);

        VBox stockBox = new VBox(4);
        Text stockLabel = new Text("Stock");
        stockLabel.getStyleClass().add("product-label");
        Text stock = new Text(String.format("%.0f %s",
                product.getQuantity() != null ? product.getQuantity() : 0,
                product.getUnit() != null ? product.getUnit() : ""));
        stock.getStyleClass().add("product-stock");
        stockBox.getChildren().addAll(stockLabel, stock);

        VBox totalBox = new VBox(4);
        Text totalLabel = new Text("Total Value");
        totalLabel.getStyleClass().add("product-label");
        Text total = new Text(String.format("%.2f", product.getTotalValue()));
        total.getStyleClass().add("product-price");
        total.setStyle("-fx-fill: #4f46e5; -fx-font-weight: 800;");
        totalBox.getChildren().addAll(totalLabel, total);

        infoRow.getChildren().addAll(priceBox, stockBox, totalBox);

        HBox categoryBox = new HBox();
        Label categoryBadge = new Label(formatCategoryName(product.getCategory().name()));
        categoryBadge.getStyleClass().add("category-badge");
        categoryBox.getChildren().add(categoryBadge);

        HBox buttonRow1 = new HBox(8);
        buttonRow1.setAlignment(Pos.CENTER_LEFT);

        Button editBtn = new Button("✏ Edit");
        editBtn.getStyleClass().add("edit-button");
        editBtn.setOnAction(e -> handleEditProduct(product));

        Button deleteBtn = new Button("🗑 Delete");
        deleteBtn.getStyleClass().add("delete-button");
        deleteBtn.setOnAction(e -> handleDeleteProduct(product));

        buttonRow1.getChildren().addAll(editBtn, deleteBtn);

        HBox buttonRow2 = new HBox(8);
        buttonRow2.setAlignment(Pos.CENTER_LEFT);

        Button qrBtn = new Button("📱 QR Code");
        qrBtn.getStyleClass().add("secondary-button");
        qrBtn.setStyle("-fx-padding: 8 12; -fx-font-size: 12px;");
        qrBtn.setOnAction(e -> handleShowQRCode(product));

        Button emailBtn = new Button("✉ Email");
        emailBtn.getStyleClass().add("secondary-button");
        emailBtn.setStyle("-fx-padding: 8 12; -fx-font-size: 12px;");
        emailBtn.setOnAction(e -> handleEmailProduct(product));

        Button whatsappBtn = new Button("💬 WhatsApp");
        whatsappBtn.getStyleClass().add("secondary-button");
        whatsappBtn.setStyle("-fx-padding: 8 12; -fx-font-size: 12px; -fx-text-fill: #25D366;");
        whatsappBtn.setOnAction(e -> handleWhatsAppProduct(product));

        Button pdfBtn = new Button("📄 PDF");
        pdfBtn.getStyleClass().add("secondary-button");
        pdfBtn.setStyle("-fx-padding: 8 12; -fx-font-size: 12px;");
        pdfBtn.setOnAction(e -> handleExportSinglePdf(product));

        buttonRow2.getChildren().addAll(qrBtn, emailBtn, whatsappBtn, pdfBtn);

        card.getChildren().addAll(infoRow, categoryBox, buttonRow1, buttonRow2);

        return card;
    }

    private void handleShowQRCode(Product product) {
        try {
            String qrContent = buildProductQRContent(product);
            WritableImage qrImage = generateQRCodeImageFX(qrContent, 300, 300);

            Stage dialog = new Stage();
            dialog.initModality(Modality.APPLICATION_MODAL);
            dialog.setTitle("QR Code - " + product.getName());

            VBox root = new VBox(16);
            root.setAlignment(Pos.CENTER);
            root.setPadding(new Insets(24));
            root.setStyle("-fx-background-color: white;");

            Text title = new Text(product.getName());
            title.setStyle("-fx-font-size: 18px; -fx-font-weight: 700; -fx-fill: #1a1d29;");

            ImageView imageView = new ImageView(qrImage);
            imageView.setFitWidth(280);
            imageView.setFitHeight(280);
            imageView.setPreserveRatio(true);

            Text info = new Text("Scan to view product details");
            info.setStyle("-fx-font-size: 13px; -fx-fill: #6b7280;");

            Button saveBtn = new Button("💾 Save QR Code");
            saveBtn.getStyleClass().add("primary-button");
            saveBtn.setOnAction(e -> saveQRCodeToFile(product, qrImage));

            root.getChildren().addAll(title, imageView, info, saveBtn);

            Scene scene = new Scene(root, 380, 450);
            scene.getStylesheets().add(getClass().getResource("/css/main-styles.css").toExternalForm());
            dialog.setScene(scene);
            dialog.setResizable(false);
            dialog.showAndWait();

        } catch (Exception e) {
            showError("Failed to generate QR code: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private String buildProductQRContent(Product product) {
        StringBuilder sb = new StringBuilder();
        sb.append("PRODUCT: ").append(product.getName()).append("\n");
        if (product.getHsCode() != null)
            sb.append("HS Code: ").append(product.getHsCode()).append("\n");
        if (product.getCategory() != null)
            sb.append("Category: ").append(formatCategoryName(product.getCategory().name())).append("\n");
        if (product.getDescription() != null)
            sb.append("Description: ").append(product.getDescription()).append("\n");
        sb.append("Price: ").append(String.format("%.2f %s", product.getUnitPrice(), product.getCurrency()))
                .append("\n");
        sb.append("Stock: ").append(String.format("%.0f %s", product.getQuantity(), product.getUnit())).append("\n");
        sb.append("Total Value: ").append(String.format("%.2f", product.getTotalValue()));
        return sb.toString();
    }

    private WritableImage generateQRCodeImageFX(String text, int width, int height) throws WriterException {
        QRCodeWriter writer = new QRCodeWriter();
        BitMatrix bitMatrix = writer.encode(text, BarcodeFormat.QR_CODE, width, height);

        WritableImage image = new WritableImage(width, height);
        PixelWriter pixelWriter = image.getPixelWriter();

        for (int y = 0; y < height; y++) {
            for (int x = 0; x < width; x++) {
                pixelWriter.setColor(x, y, bitMatrix.get(x, y) ? Color.BLACK : Color.WHITE);
            }
        }

        return image;
    }

    private void saveQRCodeToFile(Product product, WritableImage qrImage) {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Save QR Code");
        fileChooser.setInitialFileName(product.getName().replaceAll("[^a-zA-Z0-9]", "_") + "_QR.png");
        fileChooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("PNG Image", "*.png"));

        Stage stage = (Stage) productsGrid.getScene().getWindow();
        File file = fileChooser.showSaveDialog(stage);

        if (file != null) {
            try {
                BufferedImage bufferedImage = convertToBufferedImage(qrImage);
                ImageIO.write(bufferedImage, "PNG", file);
                showSuccess("QR code saved to " + file.getName());
            } catch (IOException e) {
                showError("Failed to save QR code: " + e.getMessage());
                e.printStackTrace();
            }
        }
    }

    private BufferedImage convertToBufferedImage(WritableImage fxImage) {
        int width = (int) fxImage.getWidth();
        int height = (int) fxImage.getHeight();
        BufferedImage bufferedImage = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);

        for (int y = 0; y < height; y++) {
            for (int x = 0; x < width; x++) {
                Color fxColor = fxImage.getPixelReader().getColor(x, y);
                int rgb = ((int) (fxColor.getRed() * 255) << 16) |
                        ((int) (fxColor.getGreen() * 255) << 8) |
                        (int) (fxColor.getBlue() * 255);
                bufferedImage.setRGB(x, y, rgb);
            }
        }

        return bufferedImage;
    }

    private void handleWhatsAppProduct(Product product) {
        Stage dialog = new Stage();
        dialog.initModality(Modality.APPLICATION_MODAL);
        dialog.setTitle("Send Product via WhatsApp");

        VBox root = new VBox(16);
        root.setPadding(new Insets(24));
        root.setStyle("-fx-background-color: white;");

        Text title = new Text("Send Product Info via WhatsApp");
        title.setStyle("-fx-font-size: 18px; -fx-font-weight: 700; -fx-fill: #25D366;");

        Label phoneLabel = new Label("Recipient Phone Number *");
        phoneLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #374151;");
        TextField phoneField = new TextField();
        phoneField.setPromptText("+216 XX XXX XXX (with country code)");
        phoneField.setStyle(
                "-fx-padding: 10; -fx-border-color: #e5e7eb; -fx-border-radius: 8; -fx-background-radius: 8;");

        Label msgLabel = new Label("Additional Message");
        msgLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #374151;");
        TextArea msgField = new TextArea();
        msgField.setPromptText("Optional message...");
        msgField.setPrefRowCount(3);
        msgField.setStyle("-fx-border-color: #e5e7eb; -fx-border-radius: 8; -fx-background-radius: 8;");

        VBox previewBox = new VBox(4);
        previewBox.setStyle(
                "-fx-background-color: #f0fdf4; -fx-padding: 12; -fx-background-radius: 8; -fx-border-color: #bbf7d0; -fx-border-radius: 8;");
        Text previewTitle = new Text("Message Preview:");
        previewTitle.setStyle("-fx-font-size: 12px; -fx-fill: #6b7280;");
        Text previewContent = new Text(buildWhatsAppMessage(product, ""));
        previewContent.setStyle("-fx-font-size: 12px; -fx-fill: #374151;");
        previewContent.setWrappingWidth(420);
        previewBox.getChildren().addAll(previewTitle, previewContent);

        msgField.textProperty().addListener((obs, oldVal, newVal) -> {
            previewContent.setText(buildWhatsAppMessage(product, newVal));
        });

        Label errorLabel = new Label();
        errorLabel.setStyle("-fx-text-fill: #ef4444; -fx-font-size: 13px;");
        errorLabel.setVisible(false);

        HBox btnBox = new HBox(12);
        btnBox.setAlignment(Pos.CENTER_RIGHT);
        Button cancelBtn = new Button("Cancel");
        cancelBtn.getStyleClass().add("secondary-button");
        cancelBtn.setOnAction(e -> dialog.close());

        Button sendBtn = new Button("💬 Open WhatsApp");
        sendBtn.getStyleClass().add("primary-button");
        sendBtn.setStyle("-fx-background-color: #25D366; -fx-text-fill: white;");
        sendBtn.setOnAction(e -> {
            String phone = phoneField.getText().trim().replaceAll("[^0-9+]", "");
            if (phone.isEmpty() || phone.length() < 8) {
                errorLabel.setText("Please enter a valid phone number with country code");
                errorLabel.setVisible(true);
                return;
            }
            String cleanPhone = phone.replace("+", "");
            String message = buildWhatsAppMessage(product, msgField.getText());

            try {
                String encoded = URLEncoder.encode(message, StandardCharsets.UTF_8.toString());
                String whatsappUrl = "https://wa.me/" + cleanPhone + "?text=" + encoded;
                Desktop.getDesktop().browse(new URI(whatsappUrl));
                dialog.close();
                showSuccess("WhatsApp opened for " + phone);
            } catch (Exception ex) {
                errorLabel.setText("Failed to open WhatsApp: " + ex.getMessage());
                errorLabel.setVisible(true);
            }
        });

        btnBox.getChildren().addAll(cancelBtn, sendBtn);

        root.getChildren().addAll(title, phoneLabel, phoneField,
                msgLabel, msgField, previewBox, errorLabel, btnBox);

        javafx.scene.control.ScrollPane scrollPane = new javafx.scene.control.ScrollPane(root);
        scrollPane.setFitToWidth(true);
        scrollPane.setStyle("-fx-background-color: white; -fx-border-color: transparent;");

        Scene scene = new Scene(scrollPane, 520, 600);
        scene.getStylesheets().add(getClass().getResource("/css/main-styles.css").toExternalForm());
        dialog.setScene(scene);
        dialog.setResizable(true);
        dialog.showAndWait();
    }

    private String buildWhatsAppMessage(Product product, String additionalMessage) {
        StringBuilder sb = new StringBuilder();
        if (additionalMessage != null && !additionalMessage.trim().isEmpty()) {
            sb.append(additionalMessage.trim()).append("\n\n");
        }
        sb.append("📦 *Product Information*\n\n");
        sb.append("*Name:* ").append(product.getName()).append("\n");
        sb.append("*Category:* ").append(formatCategoryName(product.getCategory().name())).append("\n");
        if (product.getHsCode() != null)
            sb.append("*HS Code:* ").append(product.getHsCode()).append("\n");
        if (product.getDescription() != null)
            sb.append("*Description:* ").append(product.getDescription()).append("\n");
        sb.append("*Unit Price:* ").append(String.format("%.2f %s", product.getUnitPrice(), product.getCurrency()))
                .append("\n");
        sb.append("*Available Stock:* ").append(String.format("%.0f %s", product.getQuantity(), product.getUnit()))
                .append("\n");
        sb.append("*Total Value:* ").append(String.format("%.2f %s", product.getTotalValue(), product.getCurrency()))
                .append("\n");
        sb.append("\n_Sent via ExportBridge_");
        return sb.toString();
    }

    private void handleEmailProduct(Product product) {
        Stage dialog = new Stage();
        dialog.initModality(Modality.APPLICATION_MODAL);
        dialog.setTitle("Email Product Info");

        VBox root = new VBox(16);
        root.setPadding(new Insets(24));
        root.setStyle("-fx-background-color: white;");

        Text title = new Text("Send Product Info by Email");
        title.setStyle("-fx-font-size: 18px; -fx-font-weight: 700; -fx-fill: #1a1d29;");

        Label toLabel = new Label("Recipient Email *");
        toLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #374151;");
        TextField toField = new TextField();
        toField.setPromptText("partner@company.com");
        toField.setStyle("-fx-padding: 10; -fx-border-color: #e5e7eb; -fx-border-radius: 8; -fx-background-radius: 8;");

        Label nameLabel = new Label("Recipient Name");
        nameLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #374151;");
        TextField nameField = new TextField();
        nameField.setPromptText("John Doe");
        nameField.setStyle(
                "-fx-padding: 10; -fx-border-color: #e5e7eb; -fx-border-radius: 8; -fx-background-radius: 8;");

        Label subLabel = new Label("Subject");
        subLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #374151;");
        TextField subField = new TextField("Product Information: " + product.getName());
        subField.setStyle(
                "-fx-padding: 10; -fx-border-color: #e5e7eb; -fx-border-radius: 8; -fx-background-radius: 8;");

        Label msgLabel = new Label("Additional Message");
        msgLabel.setStyle("-fx-font-size: 13px; -fx-font-weight: 600; -fx-text-fill: #374151;");
        TextArea msgField = new TextArea();
        msgField.setPromptText("Optional message...");
        msgField.setPrefRowCount(3);
        msgField.setStyle("-fx-border-color: #e5e7eb; -fx-border-radius: 8; -fx-background-radius: 8;");

        VBox previewBox = new VBox(4);
        previewBox.setStyle(
                "-fx-background-color: #f9fafb; -fx-padding: 12; -fx-background-radius: 8; -fx-border-color: #e5e7eb; -fx-border-radius: 8;");
        Text previewTitle = new Text("Product Details (will be included):");
        previewTitle.setStyle("-fx-font-size: 12px; -fx-fill: #6b7280;");
        Text previewContent = new Text(buildProductEmailPreview(product));
        previewContent.setStyle("-fx-font-size: 12px; -fx-fill: #374151;");
        previewContent.setWrappingWidth(400);
        previewBox.getChildren().addAll(previewTitle, previewContent);

        Label errorLabel = new Label();
        errorLabel.setStyle("-fx-text-fill: #ef4444; -fx-font-size: 13px;");
        errorLabel.setVisible(false);

        // Buttons
        HBox btnBox = new HBox(12);
        btnBox.setAlignment(Pos.CENTER_RIGHT);
        Button cancelBtn = new Button("Cancel");
        cancelBtn.getStyleClass().add("secondary-button");
        cancelBtn.setOnAction(e -> dialog.close());

        Button sendBtn = new Button("📧 Send Email");
        sendBtn.getStyleClass().add("primary-button");
        sendBtn.setOnAction(e -> {
            String email = toField.getText().trim();
            if (email.isEmpty() || !email.contains("@")) {
                errorLabel.setText("Please enter a valid email address");
                errorLabel.setVisible(true);
                return;
            }

            sendBtn.setDisable(true);
            sendBtn.setText("Sending...");

            new Thread(() -> {
                String body = buildProductEmailBody(product, msgField.getText());
                boolean success = EmailService.sendEmailToPartner(
                        email, nameField.getText().trim(),
                        subField.getText(), body);

                javafx.application.Platform.runLater(() -> {
                    if (success) {
                        dialog.close();
                        showSuccess("Product info sent to " + email);
                    } else {
                        errorLabel.setText("Failed to send email. Check SMTP settings.");
                        errorLabel.setVisible(true);
                        sendBtn.setDisable(false);
                        sendBtn.setText("📧 Send Email");
                    }
                });
            }).start();
        });

        btnBox.getChildren().addAll(cancelBtn, sendBtn);

        root.getChildren().addAll(title, toLabel, toField, nameLabel, nameField,
                subLabel, subField, msgLabel, msgField, previewBox, errorLabel, btnBox);

        javafx.scene.control.ScrollPane scrollPane = new javafx.scene.control.ScrollPane(root);
        scrollPane.setFitToWidth(true);
        scrollPane.setStyle("-fx-background-color: white; -fx-border-color: transparent;");

        Scene scene = new Scene(scrollPane, 520, 700);
        scene.getStylesheets().add(getClass().getResource("/css/main-styles.css").toExternalForm());
        dialog.setScene(scene);
        dialog.setResizable(true);
        dialog.showAndWait();
    }

    private String buildProductEmailPreview(Product product) {
        return String.format("Name: %s\nCategory: %s\nHS Code: %s\nPrice: %.2f %s\nStock: %.0f %s\nTotal Value: %.2f",
                product.getName(),
                formatCategoryName(product.getCategory().name()),
                product.getHsCode() != null ? product.getHsCode() : "N/A",
                product.getUnitPrice(), product.getCurrency(),
                product.getQuantity(), product.getUnit(),
                product.getTotalValue());
    }

    private String buildProductEmailBody(Product product, String additionalMessage) {
        StringBuilder sb = new StringBuilder();
        if (additionalMessage != null && !additionalMessage.trim().isEmpty()) {
            sb.append(additionalMessage.trim()).append("\n\n");
        }
        sb.append("--- Product Information ---\n\n");
        sb.append("Product: ").append(product.getName()).append("\n");
        sb.append("Category: ").append(formatCategoryName(product.getCategory().name())).append("\n");
        if (product.getHsCode() != null)
            sb.append("HS Code: ").append(product.getHsCode()).append("\n");
        if (product.getDescription() != null)
            sb.append("Description: ").append(product.getDescription()).append("\n");
        sb.append("Unit Price: ").append(String.format("%.2f %s", product.getUnitPrice(), product.getCurrency()))
                .append("\n");
        sb.append("Available Stock: ").append(String.format("%.0f %s", product.getQuantity(), product.getUnit()))
                .append("\n");
        sb.append("Total Value: ").append(String.format("%.2f %s", product.getTotalValue(), product.getCurrency()))
                .append("\n");
        return sb.toString();
    }

    private void handleExportSinglePdf(Product product) {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Export Product PDF");
        fileChooser.setInitialFileName(product.getName().replaceAll("[^a-zA-Z0-9]", "_") + ".pdf");
        fileChooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("PDF Documents", "*.pdf"));

        Stage stage = (Stage) productsGrid.getScene().getWindow();
        File file = fileChooser.showSaveDialog(stage);
        if (file == null)
            return;

        try {
            exportProductsToPdf(java.util.List.of(product), file);
            showSuccess("Product exported to " + file.getName());
        } catch (Exception e) {
            showError("Failed to export PDF: " + e.getMessage());
        }
    }

    @FXML
    private void handleExportPdf() {
        if (allProducts == null || allProducts.isEmpty()) {
            showError("No products to export");
            return;
        }

        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Export All Products PDF");
        fileChooser.setInitialFileName("ExportBridge_Products_Catalog.pdf");
        fileChooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("PDF Documents", "*.pdf"));

        Stage stage = (Stage) productsGrid.getScene().getWindow();
        File file = fileChooser.showSaveDialog(stage);
        if (file == null)
            return;

        try {
            exportProductsToPdf(allProducts, file);
            showSuccess("All products exported to " + file.getName());
        } catch (Exception e) {
            showError("Failed to export PDF: " + e.getMessage());
        }
    }

    private void exportProductsToPdf(List<Product> products, File file) throws Exception {
        PdfWriter writer = new PdfWriter(file);
        PdfDocument pdfDoc = new PdfDocument(writer);
        Document document = new Document(pdfDoc);

        PdfFont bold = PdfFontFactory.createFont(StandardFonts.HELVETICA_BOLD);
        PdfFont regular = PdfFontFactory.createFont(StandardFonts.HELVETICA);
        DeviceRgb primary = new DeviceRgb(79, 70, 229);

        document.add(new Paragraph("ExportBridge - Product Catalog")
                .setFont(bold).setFontSize(22).setFontColor(primary)
                .setTextAlignment(TextAlignment.CENTER)
                .setMarginBottom(5));

        document.add(new Paragraph("Generated on " + java.time.LocalDate.now())
                .setFont(regular).setFontSize(10)
                .setFontColor(ColorConstants.GRAY)
                .setTextAlignment(TextAlignment.CENTER)
                .setMarginBottom(20));

        if (products.size() > 1) {
            double totalValue = products.stream().mapToDouble(Product::getTotalValue).sum();
            double avgPrice = products.stream().mapToDouble(p -> p.getUnitPrice() != null ? p.getUnitPrice() : 0)
                    .average().orElse(0);
            long catCount = products.stream().map(Product::getCategory).distinct().count();

            Table statsTable = new Table(UnitValue.createPercentArray(new float[] { 1, 1, 1, 1 }))
                    .useAllAvailableWidth()
                    .setMarginBottom(20);

            statsTable.addCell(createStatCell("Total Products", String.valueOf(products.size()), bold, regular));
            statsTable.addCell(createStatCell("Categories", String.valueOf(catCount), bold, regular));
            statsTable.addCell(createStatCell("Total Value", String.format("%.2f", totalValue), bold, regular));
            statsTable.addCell(createStatCell("Avg Price", String.format("%.2f", avgPrice), bold, regular));

            document.add(statsTable);
        }

        Table table = new Table(UnitValue.createPercentArray(new float[] { 3, 2, 1.5f, 1.5f, 1.5f, 2 }))
                .useAllAvailableWidth();

        String[] headers = { "Product", "Category", "HS Code", "Price", "Stock", "Total Value" };
        for (String h : headers) {
            table.addHeaderCell(new com.itextpdf.layout.element.Cell()
                    .add(new Paragraph(h).setFont(bold).setFontSize(10).setFontColor(ColorConstants.WHITE))
                    .setBackgroundColor(primary).setPadding(8));
        }

        boolean alt = false;
        for (Product p : products) {
            DeviceRgb bg = alt ? new DeviceRgb(249, 250, 251) : new DeviceRgb(255, 255, 255);
            table.addCell(new com.itextpdf.layout.element.Cell()
                    .add(new Paragraph(p.getName()).setFont(regular).setFontSize(9)).setBackgroundColor(bg)
                    .setPadding(6));
            table.addCell(new com.itextpdf.layout.element.Cell()
                    .add(new Paragraph(formatCategoryName(p.getCategory().name())).setFont(regular).setFontSize(9))
                    .setBackgroundColor(bg).setPadding(6));
            table.addCell(new com.itextpdf.layout.element.Cell()
                    .add(new Paragraph(p.getHsCode() != null ? p.getHsCode() : "-").setFont(regular).setFontSize(9))
                    .setBackgroundColor(bg).setPadding(6));
            table.addCell(new com.itextpdf.layout.element.Cell()
                    .add(new Paragraph(String.format("%.2f %s", p.getUnitPrice(), p.getCurrency())).setFont(regular)
                            .setFontSize(9))
                    .setBackgroundColor(bg).setPadding(6));
            table.addCell(new com.itextpdf.layout.element.Cell()
                    .add(new Paragraph(String.format("%.0f %s", p.getQuantity(), p.getUnit())).setFont(regular)
                            .setFontSize(9))
                    .setBackgroundColor(bg).setPadding(6));
            table.addCell(
                    new com.itextpdf.layout.element.Cell().add(new Paragraph(String.format("%.2f", p.getTotalValue()))
                            .setFont(bold).setFontSize(9).setFontColor(primary)).setBackgroundColor(bg).setPadding(6));
            alt = !alt;
        }

        document.add(table);

        document.add(new Paragraph("\n© ExportBridge - International Export Management")
                .setFont(regular).setFontSize(8).setFontColor(ColorConstants.GRAY)
                .setTextAlignment(TextAlignment.CENTER));

        document.close();
    }

    private com.itextpdf.layout.element.Cell createStatCell(String label, String value, PdfFont bold, PdfFont regular) {
        return new com.itextpdf.layout.element.Cell()
                .add(new Paragraph(value).setFont(bold).setFontSize(16).setTextAlignment(TextAlignment.CENTER))
                .add(new Paragraph(label).setFont(regular).setFontSize(9).setFontColor(ColorConstants.GRAY)
                        .setTextAlignment(TextAlignment.CENTER))
                .setPadding(10)
                .setBorder(new com.itextpdf.layout.borders.SolidBorder(new DeviceRgb(229, 231, 235), 1));
    }

    @FXML
    private void handleImportPdf() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Import Products from PDF");
        fileChooser.getExtensionFilters().add(new FileChooser.ExtensionFilter("PDF Documents", "*.pdf"));

        Stage stage = (Stage) productsGrid.getScene().getWindow();
        File file = fileChooser.showOpenDialog(stage);
        if (file == null)
            return;

        try {
            List<Product> imported = importProductsFromPdf(file);
            if (imported.isEmpty()) {
                showError("No products could be parsed from the PDF.\nMake sure it's an ExportBridge product catalog.");
                return;
            }

            Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
            confirm.setTitle("Import Products");
            confirm.setHeaderText("Found " + imported.size() + " product(s)");
            confirm.setContentText("Do you want to import them into your catalog?");

            confirm.showAndWait().ifPresent(response -> {
                if (response == ButtonType.OK) {
                    int added = 0;
                    for (Product p : imported) {
                        p.setCompanyId(currentCompanyId);
                        Product created = productController.createProduct(
                                currentCompanyId, p.getName(), p.getDescription(),
                                p.getHsCode(), p.getCategory(),
                                p.getQuantity(), p.getUnit(), p.getUnitPrice());
                        if (created != null)
                            added++;
                    }
                    showSuccess(added + " product(s) imported successfully!");
                    loadProducts();
                }
            });

        } catch (Exception e) {
            showError("Failed to import PDF: " + e.getMessage());
        }
    }

    private List<Product> importProductsFromPdf(File file) throws Exception {
        List<Product> products = new ArrayList<>();

        PdfReader reader = new PdfReader(file);
        PdfDocument pdfDoc = new PdfDocument(reader);

        StringBuilder allText = new StringBuilder();
        for (int i = 1; i <= pdfDoc.getNumberOfPages(); i++) {
            allText.append(PdfTextExtractor.getTextFromPage(pdfDoc.getPage(i))).append("\n");
        }
        pdfDoc.close();

        String text = allText.toString();
        String[] lines = text.split("\n");

        for (String line : lines) {
            line = line.trim();
            if (line.isEmpty())
                continue;

            if (line.startsWith("ExportBridge") || line.startsWith("Generated") ||
                    line.startsWith("Total Products") || line.startsWith("©") ||
                    line.equals("Product") || line.contains("Category") && line.contains("HS Code")) {
                continue;
            }

            try {
                Product p = parseProductLine(line);
                if (p != null) {
                    products.add(p);
                }
            } catch (Exception ignored) {
            }
        }

        return products;
    }

    private Product parseProductLine(String line) {
        String[] parts = line.split("\\s{2,}");

        if (parts.length < 4)
            return null;

        Product p = new Product();
        p.setName(parts[0].trim());

        if (parts.length >= 2) {
            String catStr = parts[1].trim().toUpperCase().replace(" ", "_");
            try {
                p.setCategory(ProductCategory.valueOf(catStr));
            } catch (IllegalArgumentException e) {
                p.setCategory(ProductCategory.OTHER);
            }
        }

        if (parts.length >= 3) {
            String hsCandidate = parts[2].trim();
            if (hsCandidate.matches("[0-9]+\\.?[0-9]*") && hsCandidate.length() <= 10) {
                p.setHsCode(hsCandidate);
            }
        }

        for (int i = 3; i < parts.length; i++) {
            String s = parts[i].trim();
            if (s.matches("\\d+\\.?\\d*\\s*(TND|EUR|USD)")) {
                String[] priceParts = s.split("\\s+");
                p.setUnitPrice(Double.parseDouble(priceParts[0]));
                p.setCurrency(priceParts[1]);
            } else if (s.matches("\\d+\\.?\\d*\\s+[a-zA-Z]+") && p.getQuantity() == null) {
                String[] stockParts = s.split("\\s+");
                p.setQuantity(Double.parseDouble(stockParts[0]));
                if (stockParts.length > 1)
                    p.setUnit(stockParts[1]);
            }
        }

        if (p.getUnitPrice() == null)
            p.setUnitPrice(0.0);
        if (p.getQuantity() == null)
            p.setQuantity(0.0);
        if (p.getUnit() == null)
            p.setUnit("units");
        if (p.getCurrency() == null)
            p.setCurrency("TND");
        if (p.getCategory() == null)
            p.setCategory(ProductCategory.OTHER);

        if (p.getName() == null || p.getName().isEmpty())
            return null;

        return p;
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
        if (category == null)
            return "📦";
        return switch (category.name()) {
            case "OLIVE_OIL" -> "🫒";
            case "DATES" -> "🌴";
            case "TEXTILES" -> "🧵";
            case "ELECTRONICS" -> "📱";
            case "FOOD_BEVERAGE" -> "🍽️";
            case "SEAFOOD" -> "🐟";
            case "HANDICRAFTS" -> "🎨";
            case "MACHINERY" -> "⚙️";
            case "COSMETICS" -> "💄";
            case "CERAMICS" -> "🏺";
            default -> "📦";
        };
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