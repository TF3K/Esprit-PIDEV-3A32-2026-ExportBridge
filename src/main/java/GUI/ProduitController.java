package GUI;

import entites.Category;
import entites.Produit;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import javafx.scene.control.cell.PropertyValueFactory;
import services.CategoryCRUD;
import services.ProduitCRUD;

public class ProduitController {

    @FXML private TextField tfName;
    @FXML private TextField tfPrice;
    @FXML private TextField tfQty;
    @FXML private ComboBox<Category> cbCategory;

    @FXML private Label lblError; // ✅ message d'erreur rouge

    @FXML private TableView<Produit> tvProduit;
    @FXML private TableColumn<Produit, Integer> colId;
    @FXML private TableColumn<Produit, String> colName;
    @FXML private TableColumn<Produit, Double> colPrice;
    @FXML private TableColumn<Produit, Integer> colQty;
    @FXML private TableColumn<Produit, String> colCategory;

    private final ProduitCRUD produitCRUD = new ProduitCRUD();
    private final CategoryCRUD categoryCRUD = new CategoryCRUD();

    private final ObservableList<Produit> data = FXCollections.observableArrayList();

    @FXML
    public void initialize() {

        // ✅ Bloquer saisie non numérique
        blockNonNumericInputs();

        // mapping colonnes -> getters Produit
        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colName.setCellValueFactory(new PropertyValueFactory<>("name"));
        colPrice.setCellValueFactory(new PropertyValueFactory<>("price"));
        colQty.setCellValueFactory(new PropertyValueFactory<>("quantity"));
        colCategory.setCellValueFactory(new PropertyValueFactory<>("categoryName"));

        tvProduit.setItems(data);

        loadCategories();
        loadProduits();

        // click row => remplir form
        tvProduit.getSelectionModel().selectedItemProperty().addListener((obs, oldV, p) -> {
            if (p != null) {
                lblError.setText("");
                tfName.setText(p.getName());
                tfPrice.setText(String.valueOf(p.getPrice()));
                tfQty.setText(String.valueOf(p.getQuantity()));

                for (Category c : cbCategory.getItems()) {
                    if (c.getId() == p.getCategoryId()) {
                        cbCategory.getSelectionModel().select(c);
                        break;
                    }
                }
            }
        });
    }

    // ✅ empêche abc dans price/qty
    private void blockNonNumericInputs() {

        // Stock : chiffres seulement (0-9)
        tfQty.setTextFormatter(new TextFormatter<>(change -> {
            String newText = change.getControlNewText();
            if (newText.matches("\\d*")) return change; // autorisé
            return null; // refusé
        }));

        // Price : chiffres + point (ex: 12.5)
        tfPrice.setTextFormatter(new TextFormatter<>(change -> {
            String newText = change.getControlNewText();
            if (newText.matches("\\d*(\\.\\d*)?")) return change; // autorisé
            return null; // refusé
        }));
    }

    private void loadProduits() {
        data.setAll(produitCRUD.getAll());
    }

    private void loadCategories() {
        cbCategory.setItems(FXCollections.observableArrayList(categoryCRUD.getAll()));

        // afficher le name dans ComboBox
        cbCategory.setCellFactory(lv -> new ListCell<>() {
            @Override
            protected void updateItem(Category item, boolean empty) {
                super.updateItem(item, empty);
                setText(empty || item == null ? "" : item.getName());
            }
        });

        cbCategory.setButtonCell(new ListCell<>() {
            @Override
            protected void updateItem(Category item, boolean empty) {
                super.updateItem(item, empty);
                setText(empty || item == null ? "" : item.getName());
            }
        });
    }

    @FXML
    private void addProduit() {
        if (!validateForm()) return;

        Category selected = cbCategory.getValue();

        Produit p = new Produit();
        p.setName(tfName.getText().trim());
        p.setPrice(Double.parseDouble(tfPrice.getText().trim()));
        p.setQuantity(Integer.parseInt(tfQty.getText().trim()));
        p.setCategoryId(selected.getId());

        produitCRUD.add(p);

        clearForm();
        loadProduits();
    }

    @FXML
    private void updateProduit() {
        Produit selectedRow = tvProduit.getSelectionModel().getSelectedItem();
        if (selectedRow == null) {
            lblError.setText("❌ Sélectionne un produit dans le tableau.");
            return;
        }

        if (!validateForm()) return;

        Category selected = cbCategory.getValue();

        selectedRow.setName(tfName.getText().trim());
        selectedRow.setPrice(Double.parseDouble(tfPrice.getText().trim()));
        selectedRow.setQuantity(Integer.parseInt(tfQty.getText().trim()));
        selectedRow.setCategoryId(selected.getId());

        produitCRUD.update(selectedRow);

        clearForm();
        loadProduits();
    }

    @FXML
    private void deleteProduit() {
        Produit selectedRow = tvProduit.getSelectionModel().getSelectedItem();
        if (selectedRow == null) {
            lblError.setText("❌ Sélectionne un produit à supprimer.");
            return;
        }

        // (optionnel) confirmation
        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation");
        alert.setHeaderText(null);
        alert.setContentText("Supprimer ce produit ?");
        alert.showAndWait().ifPresent(btn -> {
            if (btn == ButtonType.OK) {
                produitCRUD.delete(selectedRow.getId());
                clearForm();
                loadProduits();
            }
        });
    }

    private boolean validateForm() {
        lblError.setText(""); // reset

        String name = tfName.getText() == null ? "" : tfName.getText().trim();
        String priceTxt = tfPrice.getText() == null ? "" : tfPrice.getText().trim();
        String qtyTxt = tfQty.getText() == null ? "" : tfQty.getText().trim();

        if (name.isEmpty()) {
            lblError.setText("❌ Nom du produit obligatoire");
            tfName.requestFocus();
            return false;
        }

        if (cbCategory.getValue() == null) {
            lblError.setText("❌ Choisir une catégorie");
            cbCategory.requestFocus();
            return false;
        }

        if (priceTxt.isEmpty()) {
            lblError.setText("❌ Prix obligatoire");
            tfPrice.requestFocus();
            return false;
        }

        double price;
        try {
            price = Double.parseDouble(priceTxt);
        } catch (Exception e) {
            lblError.setText("❌ Prix invalide (ex: 12.5)");
            tfPrice.requestFocus();
            return false;
        }
        if (price <= 0) {
            lblError.setText("❌ Le prix doit être > 0");
            tfPrice.requestFocus();
            return false;
        }

        if (qtyTxt.isEmpty()) {
            lblError.setText("❌ Stock obligatoire");
            tfQty.requestFocus();
            return false;
        }

        int stock;
        try {
            stock = Integer.parseInt(qtyTxt);
        } catch (Exception e) {
            lblError.setText("❌ Stock invalide (entier)");
            tfQty.requestFocus();
            return false;
        }
        if (stock < 0) {
            lblError.setText("❌ Stock doit être ≥ 0");
            tfQty.requestFocus();
            return false;
        }

        return true;
    }

    private void clearForm() {
        lblError.setText("");
        tfName.clear();
        tfPrice.clear();
        tfQty.clear();
        cbCategory.getSelectionModel().clearSelection();
        tvProduit.getSelectionModel().clearSelection();
    }
}
