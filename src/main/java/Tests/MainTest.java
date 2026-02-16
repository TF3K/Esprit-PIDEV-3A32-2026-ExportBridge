package Tests;

import Utils.DBConnection;
import entites.Category;
import entites.Produit;
import services.CategoryCRUD;
import services.ProduitCRUD;

public class MainTest {
    public static void main(String[] args) {

        // Test connexion DB
        DBConnection.getConnection();

        CategoryCRUD cdao = new CategoryCRUD();
        ProduitCRUD pdao = new ProduitCRUD();

        // ADD CATEGORY
        Category c = new Category();
        c.setName("Cosmétique");
        cdao.add(c);

        // AFFICHER CATEGORIES
        System.out.println("Categories:");
        cdao.getAll().forEach(cat -> System.out.println(cat.getId() + " - " + cat.getName()));

        // ADD PRODUIT (ex: categoryId = 1)
        Produit p = new Produit();
        p.setName("Shampoing");
        p.setPrice(12.5);
        p.setQuantity(30);
        p.setCategoryId(1);
        pdao.add(p);

        // AFFICHER PRODUITS
        System.out.println("Produits:");
        pdao.getAll().forEach(prod ->
                System.out.println(prod.getId() + " - " + prod.getName() + " - " +
                        prod.getPrice() + " - " + prod.getQuantity() + " - " +
                        prod.getCategoryName())
        );
    }
}

