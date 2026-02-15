package GUI;

import Controllers.PartnershipController;
import Controllers.CollaborationController;
import Entities.Partnership;
import Entities.Company;
import Entities.Collaboration;
import javafx.fxml.FXML;
import javafx.geometry.Insets;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Stage;

import java.util.List;

public class PartnershipDetailsDialogController {

    @FXML private Text flagText;
    @FXML private Text companyName;
    @FXML private Label statusBadge;
    @FXML private Label typeBadge;
    @FXML private Button terminateBtn;
    @FXML private Text countryText;
    @FXML private Text emailText;
    @FXML private Text phoneText;
    @FXML private Text establishedText;
    @FXML private VBox notesBox;
    @FXML private TextArea notesArea;
    @FXML private VBox collabList;

    private PartnershipController partnershipController;
    private CollaborationController collaborationController;
    private Partnership partnership;
    private Company partnerCompany;

    @FXML
    public void initialize() {
        partnershipController = new PartnershipController();
        collaborationController = new CollaborationController();
    }

    public void setPartnership(Partnership partnership, Company company) {
        this.partnership = partnership;
        this.partnerCompany = company;
        populateDetails();
    }

    private void populateDetails() {
        flagText.setText(getCountryFlag(partnerCompany.getCountry()));
        companyName.setText(partnerCompany.getCompanyName());

        statusBadge.setText(formatStatus(partnership.getStatus()));
        statusBadge.getStyleClass().add(getStatusClass(partnership.getStatus()));

        if (partnership.getType() != null) {
            typeBadge.setText(formatType(partnership.getType().name()));
            typeBadge.setManaged(true);
            typeBadge.setVisible(true);
        } else {
            typeBadge.setManaged(false);
            typeBadge.setVisible(false);
        }

        if (partnership.getStatus().name().equals("ACTIVE")) {
            terminateBtn.setManaged(true);
            terminateBtn.setVisible(true);
        }

        countryText.setText(partnerCompany.getCountry());
        emailText.setText(partnerCompany.getContactEmail() != null ? partnerCompany.getContactEmail() : "N/A");
        phoneText.setText(partnerCompany.getContactPhone() != null ? partnerCompany.getContactPhone() : "N/A");

        if (partnership.getEstablishedDate() != null) {
            establishedText.setText(partnership.getEstablishedDate().toLocalDate().toString());
        } else {
            establishedText.setText("Pending");
        }

        if (partnership.getNotes() != null && !partnership.getNotes().isEmpty()) {
            notesArea.setText(partnership.getNotes());
            notesBox.setManaged(true);
            notesBox.setVisible(true);
        }

        loadCollaborations();
    }

    private void loadCollaborations() {
        collabList.getChildren().clear();

        List<Collaboration> collaborations = collaborationController.getPartnershipCollaborations(partnership.getId());

        if (collaborations == null || collaborations.isEmpty()) {
            Text empty = new Text("No collaborations yet");
            empty.setStyle("-fx-fill: #6b7280; -fx-font-size: 14px;");
            collabList.getChildren().add(empty);
            return;
        }

        for (Collaboration collab : collaborations) {
            HBox collabCard = createCollaborationCard(collab);
            collabList.getChildren().add(collabCard);
        }
    }

    private HBox createCollaborationCard(Collaboration collab) {
        HBox card = new HBox(12);
        card.getStyleClass().add("collab-card");
        card.setPadding(new Insets(12));

        VBox content = new VBox(4);
        HBox.setHgrow(content, Priority.ALWAYS);

        Text title = new Text(collab.getTitle());
        title.setStyle("-fx-font-size: 14px; -fx-font-weight: 600; -fx-fill: #1a1d29;");

        if (collab.getDescription() != null && !collab.getDescription().isEmpty()) {
            Text desc = new Text(collab.getDescription());
            desc.setStyle("-fx-font-size: 13px; -fx-fill: #6b7280;");
            content.getChildren().add(desc);
        }

        Text dates = new Text(formatDateRange(collab));
        dates.setStyle("-fx-font-size: 12px; -fx-fill: #9ca3af;");

        content.getChildren().addAll(title, dates);

        Label statusLabel = new Label(collab.getStatus().name());
        statusLabel.getStyleClass().addAll("collab-status", "collab-status-" + collab.getStatus().name().toLowerCase());

        card.getChildren().addAll(content, statusLabel);

        return card;
    }

    @FXML
    private void handleAddCollaboration() {
        // TODO: Open collaboration dialog
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Add Collaboration");
        alert.setHeaderText("Feature Coming Soon");
        alert.setContentText("Collaboration management will be available in the next update.");
        alert.showAndWait();
    }

    @FXML
    private void handleTerminate() {
        TextInputDialog dialog = new TextInputDialog();
        dialog.setTitle("Terminate Partnership");
        dialog.setHeaderText("Terminate partnership with " + partnerCompany.getCompanyName() + "?");
        dialog.setContentText("Reason (optional):");

        dialog.showAndWait().ifPresent(reason -> {
            boolean success = partnershipController.terminatePartnership(partnership.getId(), reason);
            if (success) {
                Alert alert = new Alert(Alert.AlertType.INFORMATION);
                alert.setTitle("Success");
                alert.setContentText("Partnership terminated");
                alert.showAndWait();
                closeDialog();
            }
        });
    }

    @FXML
    private void handleClose() {
        closeDialog();
    }

    private String formatStatus(Entities.PartnershipStatus status) {
        return status.name().charAt(0) + status.name().substring(1).toLowerCase();
    }

    private String getStatusClass(Entities.PartnershipStatus status) {
        return "status-" + status.name().toLowerCase();
    }

    private String formatType(String type) {
        return type.replace("_", " ");
    }

    private String formatDateRange(Collaboration collab) {
        String start = collab.getStartDate() != null ? collab.getStartDate().toLocalDate().toString() : "TBD";
        String end = collab.getEndDate() != null ? collab.getEndDate().toLocalDate().toString() : "Ongoing";
        return start + " - " + end;
    }

    private String getCountryFlag(String country) {
        if (country == null) return "🏢";
        switch (country.toLowerCase()) {
            case "tunisia": return "🇹🇳";
            case "france": return "🇫🇷";
            case "germany": return "🇩🇪";
            case "italy": return "🇮🇹";
            case "spain": return "🇪🇸";
            default: return "🏢";
        }
    }

    private void closeDialog() {
        Stage stage = (Stage) companyName.getScene().getWindow();
        stage.close();
    }
}