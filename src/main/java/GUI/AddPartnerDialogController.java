package GUI;

import Controllers.PartnershipController;
import Controllers.CompanyController;
import Entities.Partnership;
import Entities.PartnershipType;
import Entities.Company;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.stage.Stage;

import java.util.List;

public class AddPartnerDialogController {

    @FXML private ComboBox<String> companyField;
    @FXML private ComboBox<String> typeField;
    @FXML private TextArea notesField;
    @FXML private Label errorLabel;

    private PartnershipController partnershipController;
    private CompanyController companyController;
    private Long currentCompanyId;
    private Runnable onSaveCallback;
    private List<Company> availableCompanies;

    @FXML
    public void initialize() {
        partnershipController = new PartnershipController();
        companyController = new CompanyController();

        for (PartnershipType type : PartnershipType.values()) {
            typeField.getItems().add(formatTypeName(type.name()));
        }
        typeField.setValue(formatTypeName(PartnershipType.SUPPLIER.name()));
    }

    public void setCurrentCompanyId(Long companyId) {
        this.currentCompanyId = companyId;
        loadAvailableCompanies();
    }

    public void setOnSaveCallback(Runnable callback) {
        this.onSaveCallback = callback;
    }

    private void loadAvailableCompanies() {
        availableCompanies = companyController.getActiveCompanies();

        availableCompanies.removeIf(c -> c.getId().equals(currentCompanyId));

        for (Company company : availableCompanies) {
            companyField.getItems().add(company.getCompanyName());
        }
    }

    @FXML
    private void handleSave() {
        if (!validateFields()) {
            return;
        }

        try {
            String selectedName = companyField.getValue();
            Company selectedCompany = availableCompanies.stream()
                    .filter(c -> c.getCompanyName().equals(selectedName))
                    .findFirst()
                    .orElse(null);

            if (selectedCompany == null) {
                showError("Selected company not found");
                return;
            }

            String typeStr = typeField.getValue().toUpperCase().replace(" ", "_");
            PartnershipType type = PartnershipType.valueOf(typeStr);

            String notes = notesField.getText().trim();

            Partnership partnership = partnershipController.createPartnership(
                    currentCompanyId,
                    selectedCompany.getId(),
                    type,
                    notes
            );

            if (partnership != null) {
                if (onSaveCallback != null) {
                    onSaveCallback.run();
                }
                closeDialog();
            } else {
                showError("Failed to create partnership");
            }

        } catch (Exception e) {
            showError("Error creating partnership: " + e.getMessage());
        }
    }

    @FXML
    private void handleCancel() {
        closeDialog();
    }

    private boolean validateFields() {
        if (companyField.getValue() == null || companyField.getValue().isEmpty()) {
            showError("Please select a partner company");
            return false;
        }

        if (typeField.getValue() == null || typeField.getValue().isEmpty()) {
            showError("Please select a partnership type");
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
        Stage stage = (Stage) companyField.getScene().getWindow();
        stage.close();
    }
}