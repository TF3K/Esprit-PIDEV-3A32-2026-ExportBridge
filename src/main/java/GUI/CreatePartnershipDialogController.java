package GUI;

import Controllers.PartnershipController;
import Controllers.CompanyController;
import Entities.Company;
import Entities.Partnership;
import Entities.PartnershipType;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.text.Text;
import javafx.stage.Stage;
import lombok.Setter;

public class CreatePartnershipDialogController {

    @FXML
    private Text partnerCompanyName;
    @FXML
    private Text companyDetails;
    @FXML
    private ComboBox<String> typeField;
    @FXML
    private TextArea notesField;
    @FXML
    private Label errorLabel;

    private PartnershipController partnershipController;
    private CompanyController companyController;
    private Company partnerCompany;
    @Setter
    private Long sourceCompanyId;
    @Setter
    private Runnable onSuccessCallback;

    @FXML
    public void initialize() {
        partnershipController = new PartnershipController();
        companyController = new CompanyController();

        for (PartnershipType type : PartnershipType.values()) {
            typeField.getItems().add(formatTypeName(type.name()));
        }

        typeField.setValue(formatTypeName(PartnershipType.SUPPLIER.name()));
    }

    public void setPartnerCompany(Company company) {
        this.partnerCompany = company;
        populateCompanyInfo();
    }

    private void populateCompanyInfo() {
        if (partnerCompany == null)
            return;

        partnerCompanyName.setText("Partner with " + partnerCompany.getCompanyName());

        StringBuilder details = new StringBuilder();
        details.append("Country: ").append(partnerCompany.getCountry()).append("\n");

        if (partnerCompany.getDomain() != null) {
            details.append("Industry: ").append(partnerCompany.getDomain()).append("\n");
        }

        if (partnerCompany.getAddress() != null && !partnerCompany.getAddress().isEmpty()) {
            details.append("Address: ").append(partnerCompany.getAddress());
        }

        companyDetails.setText(details.toString());
    }

    @FXML
    private void handleSend() {
        if (!validateForm()) {
            return;
        }

        try {
            Long targetCompanyId = ensureCompanyExists(partnerCompany);

            if (targetCompanyId == null) {
                showError("Failed to process partner company information");
                return;
            }

            String typeStr = typeField.getValue().toUpperCase().replace(" ", "_");
            PartnershipType type = PartnershipType.valueOf(typeStr);

            String notes = notesField.getText().trim();

            Partnership partnership = partnershipController.createPartnership(
                    sourceCompanyId,
                    type,
                    notes);

            if (partnership != null) {
                if (onSuccessCallback != null) {
                    onSuccessCallback.run();
                }
                closeDialog();
            } else {
                showError("Failed to create partnership. It may already exist.");
            }

        } catch (Exception e) {
            showError("Error creating partnership: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private Long ensureCompanyExists(Company company) {
        Company existing = companyController.findByNameAndCountry(
                company.getCompanyName(),
                company.getCountry());

        if (existing != null) {
            System.out.println("✓ Partner company already exists in database: " + existing.getId());
            return existing.getId();
        }

        System.out.println("⚙ Creating partner company in database");
        Company created = companyController.createCompany(
                company.getCompanyName(),
                company.getTaxNumber(),
                company.getRegistrationNumber(),
                company.getAddress(),
                company.getContactEmail(),
                company.getContactPhone(),
                null);

        if (created != null) {
            created.setCountry(company.getCountry());
            created.setDomain(company.getDomain());
            companyController.updateCompany(created);

            System.out.println("✓ Partner company created with ID: " + created.getId());
            return created.getId();
        }

        return null;
    }

    @FXML
    private void handleCancel() {
        closeDialog();
    }

    private boolean validateForm() {
        if (typeField.getValue() == null || typeField.getValue().isEmpty()) {
            showError("Please select a partnership type");
            return false;
        }

        if (notesField.getText().trim().isEmpty()) {
            showError("Please add a message introducing your company");
            return false;
        }

        return true;
    }

    private void showError(String message) {
        errorLabel.setText(message);
        errorLabel.setVisible(true);
        errorLabel.setManaged(true);
    }

    private String formatTypeName(String type) {
        String[] words = type.replace("_", " ").toLowerCase().split(" ");
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
        Stage stage = (Stage) typeField.getScene().getWindow();
        stage.close();
    }
}