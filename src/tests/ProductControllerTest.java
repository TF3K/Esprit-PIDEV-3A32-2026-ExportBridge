import Controllers.CompanyController;
import Controllers.ProductController;
import Entities.Product;
import Entities.ProductCategory;
import org.junit.jupiter.api.*;

import java.util.List;

import static org.junit.jupiter.api.Assertions.*;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class ProductControllerTest {

    private static ProductController productController;
    private static CompanyController companyController;
    private static Long testCompanyId;
    private static Long testProductId;

    @BeforeAll
    public static void setup() {
        productController = new ProductController();
        companyController = new CompanyController();

        var company = companyController.createCompany(
                "Product Test Company",
                "TAX_PROD_" + System.currentTimeMillis(),
                "REG_PROD_123",
                "Test Address",
                "product@test.tn",
                "+216 11 111 111",
                null
        );
        testCompanyId = company.getId();
    }

    @AfterAll
    public static void cleanup() {
        if (testCompanyId != null) {
            companyController.deleteCompany(testCompanyId);
        }
    }

    @Test
    @Order(1)
    @DisplayName("Test 1: Create New Product")
    public void testCreateProduct() {
        Product product = productController.createProduct(
                testCompanyId,
                "Premium Olive Oil",
                "Extra virgin olive oil from Tunisia",
                "1509.10",
                ProductCategory.OLIVE_OIL,
                1000.0,
                "liters",
                25.50
        );

        assertNotNull(product, "Product creation should succeed");
        assertNotNull(product.getId(), "Product should have an ID");
        assertEquals("Premium Olive Oil", product.getName());
        assertEquals(ProductCategory.OLIVE_OIL, product.getCategory());
        assertEquals(testCompanyId, product.getCompanyId());

        testProductId = product.getId();
    }

    @Test
    @Order(2)
    @DisplayName("Test 2: Get Product by ID")
    public void testGetProduct() {
        Product product = productController.getProduct(testProductId);

        assertNotNull(product, "Should retrieve product");
        assertEquals(testProductId, product.getId());
        assertEquals("Premium Olive Oil", product.getName());
    }

    @Test
    @Order(3)
    @DisplayName("Test 3: Get Company Products")
    public void testGetCompanyProducts() {
        List<Product> products = productController.getCompanyProducts(testCompanyId);

        assertNotNull(products, "Should return list");
        assertFalse(products.isEmpty(), "Should have at least one product");
        assertTrue(products.stream().anyMatch(p -> p.getId().equals(testProductId)),
                "Should contain our test product");
    }

    @Test
    @Order(4)
    @DisplayName("Test 4: Update Product")
    public void testUpdateProduct() {
        Product product = productController.getProduct(testProductId);
        assertNotNull(product);

        product.setName("Super Premium Olive Oil");
        product.setQuantity(2000.0);
        product.setUnitPrice(30.00);

        boolean updated = productController.updateProduct(product);
        assertTrue(updated, "Update should succeed");

        Product updatedProduct = productController.getProduct(testProductId);
        assertEquals("Super Premium Olive Oil", updatedProduct.getName());
        assertEquals(2000.0, updatedProduct.getQuantity());
        assertEquals(30.00, updatedProduct.getUnitPrice());
    }

    @Test
    @Order(5)
    @DisplayName("Test 5: Create Multiple Products")
    public void testCreateMultipleProducts() {
        Product dates = productController.createProduct(
                testCompanyId,
                "Deglet Nour Dates",
                "Premium quality dates",
                "0804.10",
                ProductCategory.DATES,
                500.0,
                "kg",
                15.00
        );

        Product textiles = productController.createProduct(
                testCompanyId,
                "Cotton Fabric",
                "100% cotton textile",
                "5208.11",
                ProductCategory.TEXTILES,
                1000.0,
                "meters",
                8.50
        );

        assertNotNull(dates);
        assertNotNull(textiles);

        List<Product> products = productController.getCompanyProducts(testCompanyId);
        assertTrue(products.size() >= 3, "Should have at least 3 products");
    }

    @Test
    @Order(6)
    @DisplayName("Test 6: Delete Product")
    public void testDeleteProduct() {
        boolean deleted = productController.deleteProduct(testProductId);
        assertTrue(deleted, "Delete should succeed");

        Product product = productController.getProduct(testProductId);
        assertNull(product, "Product should no longer exist");
    }
}