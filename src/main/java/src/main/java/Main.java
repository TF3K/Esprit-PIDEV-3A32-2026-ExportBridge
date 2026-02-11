package src.main.java;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;

public class Main extends Application {

    @Override
    public void start(Stage primaryStage) throws Exception {
        // Load FXML
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/auth-view.fxml"));
        Parent root = loader.load();

        // Create scene
        Scene scene = new Scene(root, 500, 800);

        // Add stylesheet
        scene.getStylesheets().add(getClass().getResource("/css/auth-styles.css").toExternalForm());

        // Setup stage
        primaryStage.setTitle("ExportBridge - Authentication");
        primaryStage.setScene(scene);
        primaryStage.setResizable(false);
        primaryStage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}