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

        Button viewBtn = new Button("View Details");
        viewBtn.getStyleClass().add("view-button");
        viewBtn.setOnAction(e -> viewCertificate(cert));

        Button renewBtn = new Button("Renew");
        renewBtn.getStyleClass().add("secondary-button");
        renewBtn.setOnAction(e -> renewCertificate(cert));
        renewBtn.setVisible(cert.getStatus() == CertificateStatus.EXPIRED || cert.isExpiringSoon());
        renewBtn.setManaged(renewBtn.isVisible());

        actionBox.getChildren().addAll(viewBtn, renewBtn);

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
}