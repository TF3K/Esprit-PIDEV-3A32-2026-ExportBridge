package GUI;

import Controllers.*;
import Utils.AppState;
import javafx.animation.TranslateTransition;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.text.Text;
import javafx.stage.Stage;
import javafx.util.Duration;

import java.io.IOException;
import java.util.Objects;

public class MainWindowController {

    @FXML private VBox sidebar;
    @FXML private Text appNameText;
    @FXML private StackPane contentArea;

    @FXML private HBox navProducts;
    @FXML private HBox navMarkets;
    @FXML private HBox navPartners;
    @FXML private HBox navSettings;

    @FXML private Text navProductsText;
    @FXML private Text navMarketsText;
    @FXML private Text navPartnersText;
    @FXML private Text navSettingsText;
    @FXML private Text navLogoutText;

    private boolean sidebarCollapsed = false;
    private HBox currentActiveNav = null;

    @FXML
    public void initialize() {
        showProducts();
    }

    @FXML
    private void toggleSidebar() {
        sidebarCollapsed = !sidebarCollapsed;

        if (sidebarCollapsed) {
            sidebar.setPrefWidth(70);
            appNameText.setVisible(false);
            appNameText.setManaged(false);

            hideNavText(true);
        } else {
            sidebar.setPrefWidth(200);
            appNameText.setVisible(true);
            appNameText.setManaged(true);

            hideNavText(false);
        }
    }

    private void hideNavText(boolean hide) {
        navProductsText.setVisible(!hide);
        navProductsText.setManaged(!hide);
        navMarketsText.setVisible(!hide);
        navMarketsText.setManaged(!hide);
        navPartnersText.setVisible(!hide);
        navPartnersText.setManaged(!hide);
        navSettingsText.setVisible(!hide);
        navSettingsText.setManaged(!hide);
        navLogoutText.setVisible(!hide);
        navLogoutText.setManaged(!hide);
    }

    @FXML
    private void showProducts() {
        setActiveNav(navProducts);
        loadView("/fxml/products-view.fxml");
    }

    @FXML
    private void showMarkets() {
        setActiveNav(navMarkets);
        loadView("/fxml/markets-view.fxml");
    }

    @FXML
    private void showPartners() {
        setActiveNav(navPartners);
        loadView("/fxml/partners-view.fxml");
    }

    @FXML
    private void showSettings() {
        setActiveNav(navSettings);
        loadView("/fxml/settings-view.fxml");
    }

    @FXML
    private void handleLogout() {
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Logout");
        alert.setHeaderText("Are you sure you want to logout?");
        alert.setContentText("You will be returned to the login screen.");

        alert.showAndWait().ifPresent(response -> {
            if (response == ButtonType.OK) {
                performLogout();
            }
        });
    }

    private void performLogout() {
        AuthenticationController authController = new AuthenticationController();
        authController.logout();

        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/auth-view.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) sidebar.getScene().getWindow();

            Scene scene = new Scene(root, 500, 700);
            String css = Objects.requireNonNull(getClass().getResource("/css/auth-styles.css")).toExternalForm();
            scene.getStylesheets().add(css);

            stage.setScene(scene);
            stage.setTitle("ExportBridge - Login");
            stage.setResizable(false);
            stage.centerOnScreen();

        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    private void setActiveNav(HBox navItem) {
        if (currentActiveNav != null) {
            currentActiveNav.getStyleClass().remove("nav-active");
        }

        navItem.getStyleClass().add("nav-active");
        currentActiveNav = navItem;
    }

    private void loadView(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent view = loader.load();

            contentArea.getChildren().clear();
            contentArea.getChildren().add(view);

        } catch (IOException e) {
            e.printStackTrace();
            System.err.println("Failed to load view: " + fxmlPath);
        }
    }
}