package GUI;

import Controllers.CertificateController;
import Entities.Certificate;
import Entities.CertificateType;
import Utils.AppState;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.stage.FileChooser;
import javafx.stage.Stage;

import java.io.File;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;

public class AddCertificateDialogController {

    @FXML private ComboBox<String> typeField;
    @FXML private TextField certificateNumberField;
    @FXML private TextField issuingAuthorityField;
    @FXML private TextField countryOfOriginField;
    @FXML private DatePicker issueDatePicker;
    @FXML private DatePicker expiryDatePicker;
    @FXML private TextField documentFileField;
    @FXML private Label errorLabel;

    private CertificateController certificateController;
    private Runnable onSuccessCallback;

    @FXML
    public void initialize() {
        certificateController = new CertificateController();

        // Populate certificate types
        populateCertificateTypes();

        // Set default dates
        issueDatePicker.setValue(LocalDate.now());
        expiryDatePicker.setValue(LocalDate.now().plusYears(1));

        // Set default country from user's company
        if (AppState.getCurrentManager() != null && AppState.getCurrentManager().getCompanyId() != null) {
            countryOfOriginField.setText("Tunisia"); // Default
        }
    }

    private void populateCertificateTypes() {
        for (CertificateType type : CertificateType.values()) {
            typeField.getItems().add(formatCertificateType(type));
        }
    }

    @FXML
    private void handleBrowseFile() {
        FileChooser fileChooser = new FileChooser();
        fileChooser.setTitle("Select Certificate Document");
        fileChooser.getExtensionFilters().addAll(
                new FileChooser.ExtensionFilter("PDF Files", "*.pdf"),
                new FileChooser.ExtensionFilter("Image Files", "*.png", "*.jpg", "*.jpeg"),
                new FileChooser.ExtensionFilter("All Files", "*.*")
        );

        File file = fileChooser.showOpenDialog(documentFileField.getScene().getWindow());

        if (file != null) {
            documentFileField.setText(file.getAbsolutePath());
        }
    }

    @FXML
    private void handleAdd() {
        if (!validateForm()) {
            return;
        }

        try {
            // Get selected type
            String selectedTypeStr = typeField.getValue();
            CertificateType selectedType = parseCertificateType(selectedTypeStr);

            // Get dates
            LocalDate issueDate = issueDatePicker.getValue();
            LocalDate expiryDate = expiryDatePicker.getValue();

            // Convert LocalDate to LocalDateTime (start and end of day)
            LocalDateTime issueDateTime = issueDate.atStartOfDay();
            LocalDateTime expiryDateTime = expiryDate.atTime(LocalTime.MAX);

            // Get company ID
            Long companyId = AppState.getCurrentManager() != null ?
                    AppState.getCurrentManager().getCompanyId() : null;

            if (companyId == null) {
                showError("You must register your company before adding certificates.");
                return;
            }

            // Create certificate
            Certificate certificate = certificateController.createCertificate(
                    selectedType,
                    certificateNumberField.getText().trim(),
                    issuingAuthorityField.getText().trim(),
                    issueDateTime,
                    expiryDateTime,
                    countryOfOriginField.getText().trim(),
                    companyId
            );

            if (certificate != null) {
                // If document file was selected, update it
                if (!documentFileField.getText().trim().isEmpty()) {
                    certificate.setDocumentFile(documentFileField.getText().trim());
                    certificateController.updateCertificate(certificate);
                }

                if (onSuccessCallback != null) {
                    onSuccessCallback.run();
                }

                closeDialog();
            } else {
                showError("Failed to create certificate. Please try again.");
            }

        } catch (Exception e) {
            showError("Error creating certificate: " + e.getMessage());
            e.printStackTrace();
        }
    }

    @FXML
    private void handleCancel() {
        closeDialog();
    }

    private boolean validateForm() {
        // Certificate Type
        if (typeField.getValue() == null || typeField.getValue().isEmpty()) {
            showError("Please select a certificate type");
            return false;
        }

        // Certificate Number
        if (certificateNumberField.getText().trim().isEmpty()) {
            showError("Please enter a certificate number");
            return false;
        }

        // Issuing Authority
        if (issuingAuthorityField.getText().trim().isEmpty()) {
            showError("Please enter the issuing authority");
            return false;
        }

        // Country of Origin
        if (countryOfOriginField.getText().trim().isEmpty()) {
            showError("Please enter the country of origin");
            return false;
        }

        // Issue Date
        if (issueDatePicker.getValue() == null) {
            showError("Please select an issue date");
            return false;
        }

        // Expiry Date
        if (expiryDatePicker.getValue() == null) {
            showError("Please select an expiry date");
            return false;
        }

        // Validate date logic
        if (expiryDatePicker.getValue().isBefore(issueDatePicker.getValue())) {
            showError("Expiry date cannot be before issue date");
            return false;
        }

        if (expiryDatePicker.getValue().isBefore(LocalDate.now())) {
            showError("Cannot create a certificate that is already expired");
            return false;
        }

        return true;
    }

    public void setOnSuccessCallback(Runnable callback) {
        this.onSuccessCallback = callback;
    }

    private void showError(String message) {
        errorLabel.setText(message);
        errorLabel.setVisible(true);
        errorLabel.setManaged(true);
    }

    private void closeDialog() {
        Stage stage = (Stage) typeField.getScene().getWindow();
        stage.close();
    }

    /**
     * Format certificate type for display
     */
    private String formatCertificateType(CertificateType type) {
        switch (type) {
            case EUR1_ORIGIN:
                return "EUR.1 Certificate of Origin";
            case EUR_MED_ORIGIN:
                return "EUR-MED Certificate of Origin";
            case CE_CONFORMITY:
                return "CE Conformity Certificate";
            case PHYTOSANITY:
                return "Phytosanitary Certificate";
            case HEALTH_CERTIFICATE:
                return "Health Certificate";
            case ISO_9001:
                return "ISO 9001 Quality Management";
            case ISO_22000:
                return "ISO 22000 Food Safety";
            case ISO_14001:
                return "ISO 14001 Environmental";
            case HACCP:
                return "HACCP Certification";
            case HALAL:
                return "Halal Certification";
            case ORGANIC_EU:
                return "EU Organic Certification";
            case ROHS:
                return "RoHS Compliance";
            case OEKO_TEX:
                return "OEKO-TEX Standard";
            case GOTS:
                return "GOTS Organic Textile";
            case REACH:
                return "REACH Compliance";
            case EXPORT_LICENCE:
                return "Export Licence";
            default:
                return type.name().replace("_", " ");
        }
    }

    /**
     * Parse display string back to CertificateType enum
     */
    private CertificateType parseCertificateType(String displayStr) {
        for (CertificateType type : CertificateType.values()) {
            if (formatCertificateType(type).equals(displayStr)) {
                return type;
            }
        }
        // Fallback - try direct enum match
        return CertificateType.valueOf(displayStr.toUpperCase().replace(" ", "_"));
    }
}