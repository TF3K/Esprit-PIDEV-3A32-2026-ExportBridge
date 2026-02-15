import Controllers.AuthenticationController;
import Entities.Manager;
import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

import java.util.Objects;

public class Main extends Application {

    @Override
    public void start(Stage primaryStage) throws Exception {
        AuthenticationController authController = new AuthenticationController();
        Manager savedManager = authController.tryRestoreSession();

        if (savedManager != null) {
            loadMainWindow(primaryStage);
        } else {
            loadAuthWindow(primaryStage);
        }
    }

    private void loadMainWindow(Stage stage) throws Exception {
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/main-window.fxml"));
        Parent root = loader.load();

        Scene scene = new Scene(root, 1400, 900);
        scene.getStylesheets().add(Objects.requireNonNull(getClass().getResource("/css/main-styles.css")).toExternalForm());

        stage.setTitle("ExportBridge - International Markets");
        stage.setScene(scene);
        stage.setResizable(true);
        stage.centerOnScreen();
        stage.show();
    }

    private void loadAuthWindow(Stage stage) throws Exception {
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/auth-view.fxml"));
        Parent root = loader.load();

        Scene scene = new Scene(root, 500, 700);
        scene.getStylesheets().add(Objects.requireNonNull(getClass().getResource("/css/auth-styles.css")).toExternalForm());

        stage.setTitle("ExportBridge - Login");
        stage.setScene(scene);
        stage.setResizable(false);
        stage.centerOnScreen();
        stage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}