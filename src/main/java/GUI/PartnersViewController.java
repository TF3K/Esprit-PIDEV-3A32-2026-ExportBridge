package GUI;

import Controllers.PartnershipController;
import Controllers.CompanyController;
import Controllers.CollaborationController;
import Entities.Partnership;
import Entities.PartnershipStatus;
import Entities.Company;
import Entities.Collaboration;
import Utils.AppState;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.geometry.Pos;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Modality;
import javafx.stage.Stage;

import java.io.IOException;
import java.util.List;
import java.util.stream.Collectors;

public class PartnersViewController {

    @FXML private TextField searchField;
    @FXML private Text resultCount;
    @FXML private Text totalCount;
    @FXML private Text activeCount;
    @FXML private Text pendingCount;
    @FXML private Text inactiveCount;
    @FXML private VBox partnersList;
    @FXML private VBox emptyState;

    @FXML private VBox allPartnersCard;
    @FXML private VBox activePartnersCard;
    @FXML private VBox pendingPartnersCard;
    @FXML private VBox inactivePartnersCard;

    private PartnershipController partnershipController;
    private CompanyController companyController;
    private CollaborationController collaborationController;
    private List<Partnership> allPartnerships;
    private Long currentCompanyId;
    private VBox activeStatCard;

    @FXML
    public void initialize() {
        partnershipController = new PartnershipController();
        companyController = new CompanyController();
        collaborationController = new CollaborationController();

        if (AppState.getCurrentManager() != null) {
            currentCompanyId = AppState.getCurrentManager().getCompanyId();
        }

        activeStatCard = allPartnersCard;

        loadPartnerships();
        updateStats();
    }

    private void loadPartnerships() {
        if (currentCompanyId == null) {
            showError("No company associated with your account");
            return;
        }

        allPartnerships = partnershipController.getCompanyPartnerships(currentCompanyId);
        displayPartnerships(allPartnerships);
    }

    private void displayPartnerships(List<Partnership> partnerships) {
        partnersList.getChildren().clear();

        if (partnerships == null || partnerships.isEmpty()) {
            partnersList.setManaged(false);
            partnersList.setVisible(false);
            emptyState.setManaged(true);
            emptyState.setVisible(true);
            resultCount.setText("0 partners");
            return;
        }

        partnersList.setManaged(true);
        partnersList.setVisible(true);
        emptyState.setManaged(false);
        emptyState.setVisible(false);

        for (Partnership partnership : partnerships) {
            HBox card = createPartnerCard(partnership);
            partnersList.getChildren().add(card);
        }

        resultCount.setText(partnerships.size() + " partner" + (partnerships.size() != 1 ? "s" : ""));
    }

    private HBox createPartnerCard(Partnership partnership) {
        HBox card = new HBox(20);
        card.getStyleClass().add("partner-card");
        card.setAlignment(Pos.CENTER_LEFT);
        card.setPrefHeight(100);

        Long partnerCompanyId = partnership.getSourceCompanyId().equals(currentCompanyId)
                ? partnership.getSourceCompanyId()
                : null;

        Company partnerCompany = companyController.getCompany(partnerCompanyId);

        if (partnerCompany == null) {
            return card;
        }

        StackPane iconBox = new StackPane();
        iconBox.getStyleClass().add("partner-icon");
        iconBox.setPrefSize(60, 60);
        Text icon = new Text(getCountryFlag(partnerCompany.getCountry()));
        icon.setStyle("-fx-font-size: 32px;");
        iconBox.getChildren().add(icon);

        VBox infoBox = new VBox(6);
        HBox.setHgrow(infoBox, Priority.ALWAYS);

        Text companyName = new Text(partnerCompany.getCompanyName());
        companyName.getStyleClass().add("partner-name");

        HBox detailsRow = new HBox(20);

        Text typeInfo = new Text((partnership.getType() != null ? partnership.getType().name() : "General").replace("_", " "));
        typeInfo.getStyleClass().add("partner-detail");

        Text countryInfo = new Text(partnerCompany.getCountry());
        countryInfo.getStyleClass().add("partner-detail");

        detailsRow.getChildren().addAll(typeInfo, countryInfo);

        infoBox.getChildren().addAll(companyName, detailsRow);

        VBox statsBox = new VBox(4);
        statsBox.setAlignment(Pos.CENTER);
        statsBox.setPrefWidth(100);

        long collabCount = 0;
        List<Collaboration> collabs = collaborationController.getPartnershipCollaborations(partnership.getId());
        if (collabs != null) {
            collabCount = collabs.size();
        }

        Text collabCountText = new Text(String.valueOf(collabCount));
        collabCountText.getStyleClass().add("stat-value-small");

        Text collabLabel = new Text("Orders");
        collabLabel.getStyleClass().add("stat-label-small");

        statsBox.getChildren().addAll(collabCountText, collabLabel);

        VBox statusBox = new VBox(4);
        statusBox.setAlignment(Pos.CENTER);
        statusBox.setPrefWidth(100);

        Label statusBadge = new Label(formatStatus(partnership.getStatus()));
        statusBadge.getStyleClass().addAll("status-badge", getStatusClass(partnership.getStatus()));

        statusBox.getChildren().add(statusBadge);

        VBox actionBox = new VBox(8);
        actionBox.setAlignment(Pos.CENTER_RIGHT);
        actionBox.setPrefWidth(120);

        Button viewBtn = new Button("View Details");
        viewBtn.getStyleClass().add("view-button");
        viewBtn.setOnAction(e -> showPartnershipDetails(partnership, partnerCompany));

        actionBox.getChildren().add(viewBtn);

        if (partnership.getStatus() == PartnershipStatus.PENDING) {
            Button acceptBtn = new Button("✓ Accept");
            acceptBtn.getStyleClass().add("accept-button");
            acceptBtn.setOnAction(e -> handleAcceptPartnership(partnership));
            actionBox.getChildren().add(acceptBtn);
        }

        card.getChildren().addAll(iconBox, infoBox, statsBox, statusBox, actionBox);

        return card;
    }

    private void showPartnershipDetails(Partnership partnership, Company partnerCompany) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/partnership-details-dialog.fxml"));
            Parent root = loader.load();

            PartnershipDetailsDialogController controller = loader.getController();
            controller.setPartnership(partnership, partnerCompany);

            Stage stage = new Stage();
            stage.setTitle("Partnership with " + partnerCompany.getCompanyName());
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root, 700, 600));
            stage.setResizable(false);
            stage.showAndWait();

            // Refresh after dialog closes
            loadPartnerships();
            updateStats();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Failed to open partnership details");
        }
    }

    @FXML
    private void handleAddPartner() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/add-partner-dialog.fxml"));
            Parent root = loader.load();

            AddPartnerDialogController controller = loader.getController();
            controller.setCurrentCompanyId(currentCompanyId);
            controller.setOnSaveCallback(() -> {
                loadPartnerships();
                updateStats();
            });

            Stage stage = new Stage();
            stage.setTitle("Add Partner");
            stage.initModality(Modality.APPLICATION_MODAL);
            stage.setScene(new Scene(root));
            stage.setResizable(false);
            stage.showAndWait();

        } catch (IOException e) {
            e.printStackTrace();
            showError("Failed to open add partner dialog");
        }
    }

    private void handleAcceptPartnership(Partnership partnership) {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Accept Partnership");
        alert.setHeaderText("Accept this partnership request?");
        alert.setContentText("This will activate the partnership.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                boolean success = partnershipController.activatePartnership(partnership.getId());
                if (success) {
                    showSuccess("Partnership accepted!");
                    loadPartnerships();
                    updateStats();
                } else {
                    showError("Failed to accept partnership");
                }
            }
        });
    }

    private void updateStats() {
        if (allPartnerships == null) return;

        int total = allPartnerships.size();
        int active = (int) allPartnerships.stream()
                .filter(p -> p.getStatus() == PartnershipStatus.ACTIVE)
                .count();
        int pending = (int) allPartnerships.stream()
                .filter(p -> p.getStatus() == PartnershipStatus.PENDING)
                .count();
        int inactive = (int) allPartnerships.stream()
                .filter(p -> p.getStatus() == PartnershipStatus.INACTIVE ||
                        p.getStatus() == PartnershipStatus.TERMINATED)
                .count();

        totalCount.setText(String.valueOf(total));
        activeCount.setText(String.valueOf(active));
        pendingCount.setText(String.valueOf(pending));
        inactiveCount.setText(String.valueOf(inactive));
    }

    @FXML
    private void filterAll() {
        setActiveStatCard(allPartnersCard);
        displayPartnerships(allPartnerships);
    }

    @FXML
    private void filterActive() {
        setActiveStatCard(activePartnersCard);
        List<Partnership> filtered = allPartnerships.stream()
                .filter(p -> p.getStatus() == PartnershipStatus.ACTIVE)
                .collect(Collectors.toList());
        displayPartnerships(filtered);
    }

    @FXML
    private void filterPending() {
        setActiveStatCard(pendingPartnersCard);
        List<Partnership> filtered = allPartnerships.stream()
                .filter(p -> p.getStatus() == PartnershipStatus.PENDING)
                .collect(Collectors.toList());
        displayPartnerships(filtered);
    }

    @FXML
    private void filterInactive() {
        setActiveStatCard(inactivePartnersCard);
        List<Partnership> filtered = allPartnerships.stream()
                .filter(p -> p.getStatus() == PartnershipStatus.INACTIVE ||
                        p.getStatus() == PartnershipStatus.TERMINATED)
                .collect(Collectors.toList());
        displayPartnerships(filtered);
    }

    @FXML
    private void handleSearch() {
        String query = searchField.getText().toLowerCase().trim();

        if (query.isEmpty()) {
            displayPartnerships(allPartnerships);
            return;
        }

        List<Partnership> filtered = allPartnerships.stream()
                .filter(p -> {
                    Long partnerId = p.getSourceCompanyId().equals(currentCompanyId)
                            ? p.getSourceCompanyId()
                            : null;
                    Company partner = companyController.getCompany(partnerId);
                    return partner != null &&
                            partner.getCompanyName().toLowerCase().contains(query);
                })
                .collect(Collectors.toList());

        displayPartnerships(filtered);
    }

    private void setActiveStatCard(VBox card) {
        if (activeStatCard != null) {
            activeStatCard.getStyleClass().remove("stat-card-selected");
        }
        card.getStyleClass().add("stat-card-selected");
        activeStatCard = card;
    }

    private String formatStatus(PartnershipStatus status) {
        switch (status) {
            case ACTIVE: return "Active";
            case PENDING: return "Pending";
            case INACTIVE: return "Inactive";
            case TERMINATED: return "Terminated";
            default: return status.name();
        }
    }

    private String getStatusClass(PartnershipStatus status) {
        switch (status) {
            case ACTIVE: return "status-active";
            case PENDING: return "status-pending";
            case INACTIVE: return "status-inactive";
            case TERMINATED: return "status-terminated";
            default: return "";
        }
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