package Controllers;

import Entities.Product;
import Entities.ProductCategory;
import Services.ProductService;

import java.sql.SQLException;
import java.util.List;

public class ProductController {
    private final ProductService productService;

    public ProductController() {
        this.productService = new ProductService();
    }

    /**
     * Create product
     */
    public Product createProduct(Long companyId, String name, String description,
                                 String hsCode, ProductCategory category,
                                 Double quantity, String unit, Double unitPrice) {
        try {
            return productService.createProduct(companyId, name, description,
                    hsCode, category, quantity, unit, unitPrice);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return null;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return null;
        }
    }

    /**
     * Get product by ID
     */
    public Product getProduct(Long id) {
        try {
            return productService.getProductById(id);
        } catch (SQLException e) {
            System.err.println("Error fetching product: " + e.getMessage());
            return null;
        }
    }

    /**
     * Get all products for a company
     */
    public List<Product> getCompanyProducts(Long companyId) {
        try {
            return productService.getCompanyProducts(companyId);
        } catch (SQLException e) {
            System.err.println("Error fetching products: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get all products
     */
    public List<Product> getAllProducts() {
        try {
            return productService.getAllProducts();
        } catch (SQLException e) {
            System.err.println("Error fetching products: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Update product
     */
    public boolean updateProduct(Product product) {
        try {
            return productService.updateProduct(product);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    /**
     * Delete product
     */
    public boolean deleteProduct(Long productId) {
        try {
            return productService.deleteProduct(productId);
        } catch (SQLException e) {
            System.err.println("Error deleting product: " + e.getMessage());
            return false;
        }
    }
}