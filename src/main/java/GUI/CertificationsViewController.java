package GUI;

import Controllers.CertificateController;
import Entities.Certificate;
import Entities.CertificateStatus;
import Entities.CertificateType;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Modality;
import javafx.stage.Stage;

import java.io.IOException;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.List;
import java.util.stream.Collectors;

import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.kernel.colors.ColorConstants;
import com.itextpdf.kernel.colors.DeviceRgb;
import com.itextpdf.kernel.font.PdfFont;
import com.itextpdf.kernel.font.PdfFontFactory;
import com.itextpdf.io.font.constants.StandardFonts;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Table;
import com.itextpdf.layout.element.Cell;
import com.itextpdf.layout.properties.TextAlignment;
import com.itextpdf.layout.properties.UnitValue;
import com.itextpdf.layout.borders.SolidBorder;
import javafx.stage.FileChooser;

import java.io.File;
import java.time.format.DateTimeFormatter;

public class CertificationsViewController {

    @FXML private Text totalCount;
    @FXML private Text activeCount;
    @FXML private Text expiringCount;
    @FXML private Text expiredCount;
    @FXML private VBox totalCard;
    @FXML private VBox activeCard;
    @FXML private VBox expiringCard;
    @FXML private VBox expiredCard;
    @FXML private TextField searchField;
    @FXML private Text resultCount;
    @FXML private VBox certificatesList;
    @FXML private VBox emptyState;

    private CertificateController certificateController;
    private List<Certificate> allCertificates;
    private VBox activeFilterCard;

    @FXML
    public void initialize() {
        certificateController = new CertificateController();
        activeFilterCard = totalCard;

        loadCertificates();
    }

    private void loadCertificates() {
        allCertificates = certificateController.getAllCertificates();

        if (allCertificates == null || allCertificates.isEmpty()) {
            showEmptyState();
            return;
        }

        updateStats();
        displayCertificates(allCertificates);
    }

    private void updateStats() {
        int total = allCertificates.size();
        int active = (int) allCertificates.stream()
                .filter(c -> c.getStatus() == CertificateStatus.VALID) // Changed from ACTIVE
                .count();
        int expiringSoon = (int) allCertificates.stream()
                .filter(Certificate::isExpiringSoon) // Use built-in method
                .count();
        int expired = (int) allCertificates.stream()
                .filter(c -> c.getStatus() == CertificateStatus.EXPIRED)
                .count();

        totalCount.setText(String.valueOf(total));
        activeCount.setText(String.valueOf(active));
        expiringCount.setText(String.valueOf(expiringSoon));
        expiredCount.setText(String.valueOf(expired));
    }

    private boolean isExpiringSoon(Certificate cert) {
        if (cert.getExpiryDate() == null) return false;
        if (cert.getStatus() != CertificateStatus.VALID) return false;

        long daysUntilExpiry = ChronoUnit.DAYS.between(LocalDate.now(), cert.getExpiryDate());
        return daysUntilExpiry >= 0 && daysUntilExpiry <= 30; // Expiring in 30 days
    }

    private void displayCertificates(List<Certificate> certificates) {
        certificatesList.getChildren().clear();

        if (certificates == null || certificates.isEmpty()) {
            showEmptyState();
            return;
        }

        certificatesList.setManaged(true);
        certificatesList.setVisible(true);
        emptyState.setManaged(false);
        emptyState.setVisible(false);

        for (Certificate cert : certificates) {
            HBox card = createCertificateCard(cert);
            certificatesList.getChildren().add(card);
        }

        resultCount.setText(certificates.size() + " certificate" + (certificates.size() != 1 ? "s" : ""));
    }

    private HBox createCertificateCard(Certificate cert) {
        HBox card = new HBox(20);
        card.getStyleClass().add("certificate-card");
        card.setAlignment(Pos.CENTER_LEFT);
        card.setPrefHeight(100);

        // Icon
        StackPane iconBox = new StackPane();
        iconBox.getStyleClass().add("certificate-icon");
        iconBox.setPrefSize(60, 60);

        Text icon = new Text(getCertificateIcon(cert.getType()));
        icon.setStyle("-fx-font-size: 32px;");
        iconBox.getChildren().add(icon);

        // Info
        VBox infoBox = new VBox(6);
        HBox.setHgrow(infoBox, Priority.ALWAYS);

        // Certificate name/type and number
        Text name = new Text(formatCertificateType(cert.getType()) + " #" + cert.getCertificateNumber());
        name.getStyleClass().add("certificate-name");

        Text country = new Text(cert.getCountryOfOrigin() != null ? "🌍 " + cert.getCountryOfOrigin() : "");
        country.getStyleClass().add("certificate-type");

        HBox detailsRow = new HBox(20);

        if (cert.getIssuingAuthority() != null) {
            Text authority = new Text("🏛️ " + cert.getIssuingAuthority());
            authority.getStyleClass().add("certificate-detail");
            detailsRow.getChildren().add(authority);
        }

        if (cert.getExpiryDate() != null) {
            String expiryText = "📅 Expires: " + cert.getExpiryDate().format(DateTimeFormatter.ofPattern("MMM dd, yyyy"));
            Text expiry = new Text(expiryText);
            expiry.getStyleClass().add("certificate-detail");

            if (cert.isExpiringSoon()) {
                expiry.setStyle("-fx-fill: #f59e0b; -fx-font-weight: 600;");
            }

            detailsRow.getChildren().add(expiry);
        }

        infoBox.getChildren().addAll(name, country, detailsRow);

        // Status Badge
        Label statusBadge = new Label(cert.getStatus().toString());
        statusBadge.getStyleClass().addAll("status-badge", getStatusClass(cert.getStatus()));

        // Actions
        VBox actionBox = new VBox(8);
        actionBox.setAlignment(Pos.CENTER_RIGHT);
        actionBox.setPrefWidth(120);

        Button pdfBtn = new Button("📄 PDF");
        pdfBtn.getStyleClass().add("secondary-button");
        pdfBtn.setOnAction(e -> handleExportSinglePdf(cert));

        Button viewBtn = new Button("View Details");
        viewBtn.getStyleClass().add("view-button");
        viewBtn.setOnAction(e -> viewCertificate(cert));

        Button renewBtn = new Button("Renew");
        renewBtn.getStyleClass().add("secondary-button");
        renewBtn.setOnAction(e -> renewCertificate(cert));
        renewBtn.setVisible(cert.getStatus() == CertificateStatus.EXPIRED || cert.isExpiringSoon());
        renewBtn.setManaged(renewBtn.isVisible());

        actionBox.getChildren().addAll(pdfBtn, viewBtn, renewBtn);

        card.getChildren().addAll(iconBox, infoBox, statusBadge, actionBox);

        return card;
    }


    private String formatCertificateType(CertificateType type) {
        return type.name().replace("_", " ");
    }

    @FXML
    private void handleAddCertificate() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/add-certificate-dialog.fxml"));
            Parent root = loader.load();

            AddCertificateDialogController controller = loader.getController();
            controller.setOnSuccessCallback(() -> {
                loadCertificates();
            });

            Stage stage = new Stage();
            stage.setTitle("Add Certificate");
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root));
            stage.setResizable(false);
            stage.showAndWait();

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void viewCertificate(Certificate cert) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Certificate Details");
        alert.setHeaderText(formatCertificateType(cert.getType()) + " #" + cert.getCertificateNumber());

        StringBuilder details = new StringBuilder();
        details.append("Type: ").append(formatCertificateType(cert.getType())).append("\n");
        details.append("Certificate Number: ").append(cert.getCertificateNumber()).append("\n\n");

        if (cert.getCountryOfOrigin() != null) {
            details.append("Country of Origin: ").append(cert.getCountryOfOrigin()).append("\n");
        }

        details.append("Issuing Authority: ").append(cert.getIssuingAuthority()).append("\n");

        if (cert.getIssueDate() != null) {
            details.append("Issue Date: ").append(cert.getIssueDate().format(DateTimeFormatter.ofPattern("MMM dd, yyyy"))).append("\n");
        }

        if (cert.getExpiryDate() != null) {
            details.append("Expiry Date: ").append(cert.getExpiryDate().format(DateTimeFormatter.ofPattern("MMM dd, yyyy"))).append("\n");
        }

        details.append("\nStatus: ").append(cert.getStatus());

        if (cert.getExpiryDate() != null && cert.getStatus() == CertificateStatus.VALID) {
            long daysLeft = ChronoUnit.DAYS.between(LocalDateTime.now(), cert.getExpiryDate());
            details.append("\n\nDays until expiry: ").append(daysLeft);

            if (cert.isExpiringSoon()) {
                details.append(" ⚠️ EXPIRING SOON");
            }
        }

        if (cert.getDocumentFile() != null) {
            details.append("\n\nDocument: ").append(cert.getDocumentFile());
        }

        alert.setContentText(details.toString());

        DialogPane dialogPane = alert.getDialogPane();
        dialogPane.setMinWidth(500);

        alert.showAndWait();
    }

    private void renewCertificate(Certificate cert) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION);
        confirm.setTitle("Renew Certificate");
        confirm.setHeaderText("Renew " + formatCertificateType(cert.getType()) + " #" + cert.getCertificateNumber() + "?");
        confirm.setContentText("This will extend the certificate validity by 1 year.");

        confirm.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                LocalDateTime newExpiry = LocalDateTime.now().plusYears(1);
                boolean success = certificateController.renewCertificate(cert.getId(), newExpiry);

                if (success) {
                    Alert success_alert = new Alert(Alert.AlertType.INFORMATION);
                    success_alert.setTitle("Success");
                    success_alert.setHeaderText("Certificate Renewed");
                    success_alert.setContentText("Certificate has been renewed until " +
                            newExpiry.format(DateTimeFormatter.ofPattern("MMM dd, yyyy")));
                    success_alert.showAndWait();

                    // Reload certificates
                    loadCertificates();
                } else {
                    Alert error = new Alert(Alert.AlertType.ERROR);
                    error.setTitle("Error");
                    error.setContentText("Failed to renew certificate. Please try again.");
                    error.showAndWait();
                }
            }
        });
    }


    @FXML
    private void filterAll() {
        setActiveFilter(totalCard);
        displayCertificates(allCertificates);
    }

    @FXML
    private void filterActive() {
        setActiveFilter(activeCard);
        List<Certificate> filtered = allCertificates.stream()
                .filter(c -> c.getStatus() == CertificateStatus.VALID) // Changed from ACTIVE
                .collect(Collectors.toList());
        displayCertificates(filtered);
    }

    @FXML
    private void filterExpiringSoon() {
        setActiveFilter(expiringCard);
        List<Certificate> filtered = allCertificates.stream()
                .filter(Certificate::isExpiringSoon) // Use built-in method
                .collect(Collectors.toList());
        displayCertificates(filtered);
    }

    @FXML
    private void filterExpired() {
        setActiveFilter(expiredCard);
        List<Certificate> filtered = allCertificates.stream()
                .filter(c -> c.getStatus() == CertificateStatus.EXPIRED)
                .collect(Collectors.toList());
        displayCertificates(filtered);
    }

    @FXML
    private void handleSearch() {
        String query = searchField.getText().toLowerCase().trim();

        if (query.isEmpty()) {
            displayCertificates(allCertificates);
            return;
        }

        List<Certificate> filtered = allCertificates.stream()
                .filter(c -> formatCertificateType(c.getType()).toLowerCase().contains(query) ||
                        c.getCertificateNumber().toLowerCase().contains(query) ||
                        (c.getIssuingAuthority() != null && c.getIssuingAuthority().toLowerCase().contains(query)) ||
                        (c.getCountryOfOrigin() != null && c.getCountryOfOrigin().toLowerCase().contains(query)))
                .collect(Collectors.toList());

        displayCertificates(filtered);
    }

    private void setActiveFilter(VBox card) {
        if (activeFilterCard != null) {
            activeFilterCard.getStyleClass().remove("stat-card-selected");
        }
        card.getStyleClass().add("stat-card-selected");
        activeFilterCard = card;
    }

    private void showEmptyState() {
        certificatesList.setManaged(false);
        certificatesList.setVisible(false);
        emptyState.setManaged(true);
        emptyState.setVisible(true);
        resultCount.setText("0 certificates");

        totalCount.setText("0");
        activeCount.setText("0");
        expiringCount.setText("0");
        expiredCount.setText("0");
    }

    private String getCertificateIcon(CertificateType type) {
        switch (type) {
            case EUR1_ORIGIN:
            case EUR_MED_ORIGIN:
                return "🇪🇺";
            case CE_CONFORMITY:
                return "✅";
            case PHYTOSANITY:
                return "🌱";
            case HEALTH_CERTIFICATE:
                return "🏥";
            case ISO_9001:
            case ISO_22000:
            case ISO_14001:
                return "⭐";
            case HACCP:
                return "🛡️";
            case HALAL:
                return "☪️";
            case ORGANIC_EU:
                return "🌿";
            case ROHS:
            case REACH:
                return "♻️";
            case OEKO_TEX:
            case GOTS:
                return "👕";
            case EXPORT_LICENCE:
                return "📋";
            default:
                return "📜";
        }
    }

    private String getStatusClass(CertificateStatus status) {
        return switch (status) {
            case VALID -> "status-active";
            case EXPIRED -> "status-expired";
            case PENDING -> "status-pending";
            case REJECTED -> "status-rejected";
            case REVOKED -> "status-revoked";
            default -> "";
        };
    }

    @FXML
    private void handleExportPdf() {
        if (allCertificates == null || allCertificates.isEmpty()) {
            showError("No certificates to export");
            return;
        }

        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Export Certificates to PDF");
        fileChooser.setInitialFileName("ExportBridge_Certificates_" +
                java.time.LocalDate.now() + ".pdf");
        fileChooser.getExtensionFilters().add(
                new FileChooser.ExtensionFilter("PDF Documents", "*.pdf")
        );

        File file = fileChooser.showSaveDialog(certificatesList.getScene().getWindow());
        if (file == null) return;

        try {
            exportCertificatesToPdf(allCertificates, file);
            showSuccess("Certificates exported to " + file.getName());
        } catch (Exception e) {
            showError("Failed to export PDF: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void handleExportSinglePdf(Certificate cert) {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Export Certificate to PDF");
        fileChooser.setInitialFileName(
                formatCertificateType(cert.getType()).replaceAll("[^a-zA-Z0-9]", "_") +
                        "_" + cert.getCertificateNumber() + ".pdf"
        );
        fileChooser.getExtensionFilters().add(
                new FileChooser.ExtensionFilter("PDF Documents", "*.pdf")
        );

        File file = fileChooser.showSaveDialog(certificatesList.getScene().getWindow());
        if (file == null) return;

        try {
            exportCertificatesToPdf(List.of(cert), file);
            showSuccess("Certificate exported to " + file.getName());
        } catch (Exception e) {
            showError("Failed to export PDF: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void exportCertificatesToPdf(List<Certificate> certificates, File file) throws Exception {
        PdfWriter writer = new PdfWriter(file);
        PdfDocument pdfDoc = new PdfDocument(writer);
        Document document = new Document(pdfDoc);

        // Fonts
        PdfFont bold = PdfFontFactory.createFont(StandardFonts.HELVETICA_BOLD);
        PdfFont regular = PdfFontFactory.createFont(StandardFonts.HELVETICA);
        DeviceRgb primary = new DeviceRgb(79, 70, 229);
        DeviceRgb green = new DeviceRgb(16, 185, 129);
        DeviceRgb red = new DeviceRgb(239, 68, 68);
        DeviceRgb orange = new DeviceRgb(245, 158, 11);

        // Title
        document.add(new Paragraph("ExportBridge - Certificates Report")
                .setFont(bold)
                .setFontSize(24)
                .setFontColor(primary)
                .setTextAlignment(TextAlignment.CENTER)
                .setMarginBottom(5));

        document.add(new Paragraph("Export Compliance & Quality Certifications")
                .setFont(regular)
                .setFontSize(12)
                .setFontColor(ColorConstants.GRAY)
                .setTextAlignment(TextAlignment.CENTER)
                .setMarginBottom(5));

        document.add(new Paragraph("Generated on " + java.time.LocalDateTime.now()
                .format(DateTimeFormatter.ofPattern("MMM dd, yyyy HH:mm")))
                .setFont(regular)
                .setFontSize(10)
                .setFontColor(ColorConstants.GRAY)
                .setTextAlignment(TextAlignment.CENTER)
                .setMarginBottom(20));

        // Statistics summary (if multiple certificates)
        if (certificates.size() > 1) {
            int total = certificates.size();
            long active = certificates.stream()
                    .filter(c -> c.getStatus() == CertificateStatus.VALID)
                    .count();
            long expired = certificates.stream()
                    .filter(c -> c.getStatus() == CertificateStatus.EXPIRED)
                    .count();
            long pending = certificates.stream()
                    .filter(c -> c.getStatus() == CertificateStatus.PENDING)
                    .count();

            Table statsTable = new Table(UnitValue.createPercentArray(new float[]{1, 1, 1, 1}))
                    .useAllAvailableWidth()
                    .setMarginBottom(20);

            statsTable.addCell(createStatCell("Total", String.valueOf(total), bold, regular));
            statsTable.addCell(createStatCell("Valid", String.valueOf(active), bold, regular, green));
            statsTable.addCell(createStatCell("Expired", String.valueOf(expired), bold, regular, red));
            statsTable.addCell(createStatCell("Pending", String.valueOf(pending), bold, regular, orange));

            document.add(statsTable);
        }

        // Certificates table
        if (certificates.size() > 1) {
            // Compact table for multiple certificates
            addCompactCertificatesTable(document, certificates, bold, regular, primary);
        } else {
            // Detailed view for single certificate
            addDetailedCertificateView(document, certificates.get(0), bold, regular, primary);
        }

        // Footer
        document.add(new Paragraph("\n© " + java.time.Year.now().getValue() +
                " ExportBridge - International Export Management")
                .setFont(regular)
                .setFontSize(8)
                .setFontColor(ColorConstants.GRAY)
                .setTextAlignment(TextAlignment.CENTER)
                .setMarginTop(20));

        document.close();
    }

    private void addCompactCertificatesTable(Document document, List<Certificate> certificates,
                                             PdfFont bold, PdfFont regular, DeviceRgb primary) {

        Table table = new Table(UnitValue.createPercentArray(
                new float[]{3, 2, 2, 2, 2, 1.5f}))
                .useAllAvailableWidth();

        // Header
        String[] headers = {"Type", "Number", "Authority", "Country", "Expiry Date", "Status"};
        for (String h : headers) {
            table.addHeaderCell(new Cell()
                    .add(new Paragraph(h).setFont(bold).setFontSize(10).setFontColor(ColorConstants.WHITE))
                    .setBackgroundColor(primary)
                    .setPadding(8));
        }

        // Rows
        DateTimeFormatter dateFormat = DateTimeFormatter.ofPattern("MMM dd, yyyy");
        boolean alt = false;

        for (Certificate cert : certificates) {
            DeviceRgb bg = alt ? new DeviceRgb(249, 250, 251) : new DeviceRgb(255, 255, 255);

            // Type
            table.addCell(new Cell()
                    .add(new Paragraph(formatCertificateType(cert.getType()))
                            .setFont(regular)
                            .setFontSize(9))
                    .setBackgroundColor(bg)
                    .setPadding(6));

            // Number
            table.addCell(new Cell()
                    .add(new Paragraph(cert.getCertificateNumber())
                            .setFont(regular)
                            .setFontSize(9))
                    .setBackgroundColor(bg)
                    .setPadding(6));

            // Authority
            table.addCell(new Cell()
                    .add(new Paragraph(cert.getIssuingAuthority() != null ?
                            cert.getIssuingAuthority() : "-")
                            .setFont(regular)
                            .setFontSize(9))
                    .setBackgroundColor(bg)
                    .setPadding(6));

            // Country
            table.addCell(new Cell()
                    .add(new Paragraph(cert.getCountryOfOrigin() != null ?
                            cert.getCountryOfOrigin() : "-")
                            .setFont(regular)
                            .setFontSize(9))
                    .setBackgroundColor(bg)
                    .setPadding(6));

            // Expiry Date
            String expiryStr = cert.getExpiryDate() != null ?
                    cert.getExpiryDate().format(dateFormat) : "No expiry";
            table.addCell(new Cell()
                    .add(new Paragraph(expiryStr)
                            .setFont(regular)
                            .setFontSize(9))
                    .setBackgroundColor(bg)
                    .setPadding(6));

            // Status
            DeviceRgb statusColor = getStatusColor(cert.getStatus());
            table.addCell(new Cell()
                    .add(new Paragraph(cert.getStatus().toString())
                            .setFont(bold)
                            .setFontSize(9)
                            .setFontColor(statusColor))
                    .setBackgroundColor(bg)
                    .setPadding(6));

            alt = !alt;
        }

        document.add(table);
    }

    private void addDetailedCertificateView(Document document, Certificate cert,
                                            PdfFont bold, PdfFont regular, DeviceRgb primary) {

        DateTimeFormatter dateFormat = DateTimeFormatter.ofPattern("MMMM dd, yyyy HH:mm");

        // Certificate header
        document.add(new Paragraph(formatCertificateType(cert.getType()))
                .setFont(bold)
                .setFontSize(18)
                .setFontColor(primary)
                .setMarginBottom(5));

        document.add(new Paragraph("Certificate #" + cert.getCertificateNumber())
                .setFont(regular)
                .setFontSize(12)
                .setFontColor(ColorConstants.GRAY)
                .setMarginBottom(20));

        // Status badge
        DeviceRgb statusBg = getStatusBackgroundColor(cert.getStatus());
        DeviceRgb statusColor = getStatusColor(cert.getStatus());

        Table statusTable = new Table(1)
                .setWidth(150)
                .setMarginBottom(20);

        statusTable.addCell(new Cell()
                .add(new Paragraph(cert.getStatus().toString())
                        .setFont(bold)
                        .setFontSize(12)
                        .setFontColor(statusColor)
                        .setTextAlignment(TextAlignment.CENTER))
                .setBackgroundColor(statusBg)
                .setPadding(10)
                .setBorder(new SolidBorder(statusColor, 2)));

        document.add(statusTable);

        // Details table
        Table detailsTable = new Table(UnitValue.createPercentArray(new float[]{1, 2}))
                .useAllAvailableWidth()
                .setMarginBottom(20);

        addDetailRow(detailsTable, "Issuing Authority", cert.getIssuingAuthority(), bold, regular);
        addDetailRow(detailsTable, "Country of Origin", cert.getCountryOfOrigin(), bold, regular);

        if (cert.getIssueDate() != null) {
            addDetailRow(detailsTable, "Issue Date",
                    cert.getIssueDate().format(dateFormat), bold, regular);
        }

        if (cert.getExpiryDate() != null) {
            addDetailRow(detailsTable, "Expiry Date",
                    cert.getExpiryDate().format(dateFormat), bold, regular);

            // Days until expiry
            if (cert.getStatus() == CertificateStatus.VALID) {
                long daysLeft = ChronoUnit.DAYS.between(
                        java.time.LocalDateTime.now(),
                        cert.getExpiryDate()
                );

                String daysText = daysLeft + " days remaining";
                if (cert.isExpiringSoon()) {
                    daysText += " ⚠️ EXPIRING SOON";
                }
                addDetailRow(detailsTable, "Validity", daysText, bold, regular);
            }
        }

        if (cert.getDocumentFile() != null && !cert.getDocumentFile().isEmpty()) {
            addDetailRow(detailsTable, "Document File", cert.getDocumentFile(), bold, regular);
        }

        document.add(detailsTable);

        // Certificate icon/seal
        document.add(new Paragraph("🏆")
                .setFontSize(64)
                .setTextAlignment(TextAlignment.CENTER)
                .setMarginTop(20)
                .setMarginBottom(10));

        document.add(new Paragraph("Certified by " + cert.getIssuingAuthority())
                .setFont(regular)
                .setFontSize(10)
                .setFontColor(ColorConstants.GRAY)
                .setTextAlignment(TextAlignment.CENTER));
    }

    private void addDetailRow(Table table, String label, String value,
                              PdfFont bold, PdfFont regular) {
        if (value == null || value.isEmpty()) {
            value = "-";
        }

        table.addCell(new Cell()
                .add(new Paragraph(label)
                        .setFont(bold)
                        .setFontSize(11))
                .setPadding(8)
                .setBackgroundColor(new DeviceRgb(249, 250, 251)));

        table.addCell(new Cell()
                .add(new Paragraph(value)
                        .setFont(regular)
                        .setFontSize(11))
                .setPadding(8));
    }

    private Cell createStatCell(String label, String value, PdfFont bold, PdfFont regular) {
        return createStatCell(label, value, bold, regular, new DeviceRgb(79, 70, 229));
    }

    private Cell createStatCell(String label, String value, PdfFont bold,
                                PdfFont regular, DeviceRgb color) {
        return new Cell()
                .add(new Paragraph(value)
                        .setFont(bold)
                        .setFontSize(18)
                        .setFontColor(color)
                        .setTextAlignment(TextAlignment.CENTER))
                .add(new Paragraph(label)
                        .setFont(regular)
                        .setFontSize(9)
                        .setFontColor(ColorConstants.GRAY)
                        .setTextAlignment(TextAlignment.CENTER))
                .setPadding(12)
                .setBorder(new SolidBorder(new DeviceRgb(229, 231, 235), 1));
    }

    private DeviceRgb getStatusColor(CertificateStatus status) {
        switch (status) {
            case VALID: return new DeviceRgb(5, 150, 105);
            case EXPIRED: return new DeviceRgb(220, 38, 38);
            case PENDING: return new DeviceRgb(217, 119, 6);
            case REJECTED: return new DeviceRgb(153, 27, 27);
            case REVOKED: return new DeviceRgb(107, 114, 128);
            default: return new DeviceRgb(107, 114, 128);
        }
    }

    private DeviceRgb getStatusBackgroundColor(CertificateStatus status) {
        switch (status) {
            case VALID: return new DeviceRgb(209, 250, 229);
            case EXPIRED: return new DeviceRgb(254, 226, 226);
            case PENDING: return new DeviceRgb(254, 243, 199);
            case REJECTED: return new DeviceRgb(254, 202, 202);
            case REVOKED: return new DeviceRgb(243, 244, 246);
            default: return new DeviceRgb(243, 244, 246);
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