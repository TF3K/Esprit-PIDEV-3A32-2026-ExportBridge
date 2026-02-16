

package GUI;

import Utils.DBConnection;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.TextField;

import java.sql.Connection;
import java.sql.PreparedStatement;

public class CategoryController {

    @FXML private TextField tfId;
    @FXML private TextField tfName;

    // ================= ADD =================
    @FXML
    public void handleAdd() {
        try {
            String name = tfName.getText().trim();
            if (name.isEmpty()) {
                show(Alert.AlertType.WARNING, "Le nom de catégorie est vide !");
                return;
            }

            String sql = "INSERT INTO category(name) VALUES (?)";
            Connection cnx = DBConnection.getConnection();

            try (PreparedStatement ps = cnx.prepareStatement(sql)) {
                ps.setString(1, name);
                ps.executeUpdate();
            }

            show(Alert.AlertType.INFORMATION, "Catégorie ajoutée !");
            tfName.clear();

        } catch (Exception e) {
            e.printStackTrace();
            show(Alert.AlertType.ERROR, "Erreur Add : " + e.getMessage());
        }
    }

    // ================= UPDATE =================
    @FXML
    public void handleUpdate() {
        try {
            Integer id = getIdOrNull();
            if (id == null) return;

            String name = tfName.getText().trim();
            if (name.isEmpty()) {
                show(Alert.AlertType.WARNING, "Le nom de catégorie est vide !");
                return;
            }

            String sql = "UPDATE category SET name=? WHERE id=?";
            Connection cnx = DBConnection.getConnection();

            int rows;
            try (PreparedStatement ps = cnx.prepareStatement(sql)) {
                ps.setString(1, name);
                ps.setInt(2, id);
                rows = ps.executeUpdate();
            }

            if (rows > 0) {
                show(Alert.AlertType.INFORMATION, "Catégorie modifiée !");
            } else {
                show(Alert.AlertType.WARNING, "Aucune catégorie trouvée avec ID = " + id);
            }

        } catch (Exception e) {
            e.printStackTrace();
            show(Alert.AlertType.ERROR, "Erreur Update : " + e.getMessage());
        }
    }

    // ================= DELETE =================
    @FXML
    public void handleDelete() {
        try {
            Integer id = getIdOrNull();
            if (id == null) return;

            String sql = "DELETE FROM category WHERE id=?";
            Connection cnx = DBConnection.getConnection();

            int rows;
            try (PreparedStatement ps = cnx.prepareStatement(sql)) {
                ps.setInt(1, id);
                rows = ps.executeUpdate();
            }

            if (rows > 0) {
                show(Alert.AlertType.INFORMATION, "Catégorie supprimée !");
                tfId.clear();
                tfName.clear();
            } else {
                show(Alert.AlertType.WARNING, "Aucune catégorie trouvée avec ID = " + id);
            }

        } catch (Exception e) {
            e.printStackTrace();
            show(Alert.AlertType.ERROR, "Erreur Delete : " + e.getMessage());
        }
    }

    // ================= HELPERS =================
    private Integer getIdOrNull() {
        String idText = tfId.getText();
        if (idText == null || idText.trim().isEmpty()) {
            show(Alert.AlertType.WARNING, "Le champ ID est vide !");
            return null;
        }
        try {
            return Integer.parseInt(idText.trim());
        } catch (NumberFormatException e) {
            show(Alert.AlertType.WARNING, "ID doit être un nombre !");
            return null;
        }
    }

    private void show(Alert.AlertType type, String msg) {
        Alert alert = new Alert(type);
        alert.setTitle("Info");
        alert.setHeaderText(null);
        alert.setContentText(msg);
        alert.showAndWait();
    }
}
