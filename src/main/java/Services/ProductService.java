package Services;

import DAO.ProductDAO;
import Entities.Product;
import Entities.ProductCategory;

import java.sql.SQLException;
import java.util.List;

public class ProductService {
    private final ProductDAO productDAO;

    public ProductService() {
        this.productDAO = new ProductDAO();
    }

    /**
     * Create a new product
     */
    public Product createProduct(Long companyId, String name, String description,
                                 String hsCode, ProductCategory category,
                                 Double quantity, String unit, Double unitPrice) throws SQLException {

        // Validation
        if (name == null || name.trim().isEmpty()) {
            throw new IllegalArgumentException("Product name is required");
        }

        if (category == null) {
            throw new IllegalArgumentException("Product category is required");
        }

        if (companyId == null) {
            throw new IllegalArgumentException("Company ID is required");
        }

        // Create product
        Product product = new Product();
        product.setCompanyId(companyId);
        product.setName(name);
        product.setDescription(description);
        product.setHsCode(hsCode);
        product.setCategory(category);
        product.setQuantity(quantity != null ? quantity : 0.0);
        product.setUnit(unit);
        product.setUnitPrice(unitPrice != null ? unitPrice : 0.0);
        product.setCurrency("TND");

        return productDAO.create(product);
    }

    /**
     * Get product by ID
     */
    public Product getProductById(Long id) throws SQLException {
        return productDAO.findById(id);
    }

    /**
     * Get all products for a company
     */
    public List<Product> getCompanyProducts(Long companyId) throws SQLException {
        return productDAO.findByCompanyId(companyId);
    }

    /**
     * Get all products
     */
    public List<Product> getAllProducts() throws SQLException {
        return productDAO.findAll();
    }

    /**
     * Get products by category
     */
    public List<Product> getProductsByCategory(ProductCategory category) throws SQLException {
        return productDAO.findByCategory(category);
    }

    /**
     * Update product
     */
    public boolean updateProduct(Product product) throws SQLException {
        if (product.getId() == null) {
            throw new IllegalArgumentException("Product ID is required for update");
        }

        if (product.getName() == null || product.getName().trim().isEmpty()) {
            throw new IllegalArgumentException("Product name is required");
        }

        return productDAO.update(product);
    }

    /**
     * Delete product
     */
    public boolean deleteProduct(Long productId) throws SQLException {
        return productDAO.delete(productId);
    }

    /**
     * Count company products
     */
    public long countCompanyProducts(Long companyId) throws SQLException {
        return productDAO.findByCompanyId(companyId).size();
    }
}