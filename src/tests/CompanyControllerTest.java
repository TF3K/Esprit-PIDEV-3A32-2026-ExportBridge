import Controllers.CompanyController;
import Entities.Company;
import org.junit.jupiter.api.*;

import java.util.List;

import static org.junit.jupiter.api.Assertions.*;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class CompanyControllerTest {

    private static CompanyController companyController;
    private static Long testCompanyId;
    private static String testTaxNumber;

    @BeforeAll
    public static void setup() {
        companyController = new CompanyController();
        testTaxNumber = "TAX_" + System.currentTimeMillis();
    }

    @Test
    @Order(1)
    @DisplayName("Test 1: Create New Company")
    public void testCreateCompany() {

        Company company = companyController.createCompany(
                "Test Export Company",
                testTaxNumber,
                "REG123456",
                "123 Test Street, Tunis",
                "contact@testcompany.tn",
                "+216 12 345 678",
                null
        );

        assertNotNull(company, "Company creation should succeed");
        assertNotNull(company.getId(), "Company should have an ID");
        assertEquals("Test Export Company", company.getCompanyName());
        assertEquals(testTaxNumber, company.getTaxNumber());
        assertEquals("Tunisia", company.getCountry());

        testCompanyId = company.getId();
    }

    @Test
    @Order(2)
    @DisplayName("Test 2: Create Company with Duplicate Tax Number")
    public void testCreateDuplicateTaxNumber() {
        Company company = companyController.createCompany(
                "Another Company",
                testTaxNumber,
                "REG789012",
                "456 Another Street",
                "another@company.tn",
                "+216 98 765 432",
                null
        );

        assertNull(company, "Company creation should fail for duplicate tax number");
    }

    @Test
    @Order(3)
    @DisplayName("Test 3: Get Company by ID")
    public void testGetCompanyById() {
        Company company = companyController.getCompany(testCompanyId);

        assertNotNull(company, "Should retrieve company");
        assertEquals(testCompanyId, company.getId());
        assertEquals("Test Export Company", company.getCompanyName());
    }

    @Test
    @Order(4)
    @DisplayName("Test 4: Get All Companies")
    public void testGetAllCompanies() {
        List<Company> companies = companyController.getAllCompanies();

        assertNotNull(companies, "Should return list");
        assertFalse(companies.isEmpty(), "List should not be empty");
        assertTrue(companies.stream().anyMatch(c -> c.getId().equals(testCompanyId)),
                "Should contain our test company");
    }

    @Test
    @Order(5)
    @DisplayName("Test 5: Search Companies")
    public void testSearchCompanies() {
        List<Company> results = companyController.searchCompanies("Test Export");

        assertNotNull(results, "Should return results");
        assertFalse(results.isEmpty(), "Should find matching companies");
        assertTrue(results.stream().anyMatch(c -> c.getCompanyName().contains("Test Export")),
                "Should contain matching company");
    }

    @Test
    @Order(6)
    @DisplayName("Test 6: Update Company")
    public void testUpdateCompany() {
        Company company = companyController.getCompany(testCompanyId);
        assertNotNull(company);

        company.setCompanyName("Updated Export Company");
        company.setAddress("789 Updated Street, Tunis");
        company.setRating(4);

        boolean updated = companyController.updateCompany(company);
        assertTrue(updated, "Update should succeed");

        // Verify update
        Company updatedCompany = companyController.getCompany(testCompanyId);
        assertEquals("Updated Export Company", updatedCompany.getCompanyName());
        assertEquals("789 Updated Street, Tunis", updatedCompany.getAddress());
        assertEquals(4, updatedCompany.getRating());
    }

    @Test
    @Order(7)
    @DisplayName("Test 7: Update Company Rating")
    public void testUpdateRating() {
        boolean updated = companyController.updateRating(testCompanyId, 5);
        assertTrue(updated, "Rating update should succeed");

        Company company = companyController.getCompany(testCompanyId);
        assertEquals(5, company.getRating());
    }

    @Test
    @Order(8)
    @DisplayName("Test 8: Ban Company")
    public void testBanCompany() {
        boolean banned = companyController.setBanStatus(testCompanyId, true);
        assertTrue(banned, "Ban should succeed");

        Company company = companyController.getCompany(testCompanyId);
        assertTrue(company.isBanned(), "Company should be banned");
    }

    @Test
    @Order(9)
    @DisplayName("Test 9: Unban Company")
    public void testUnbanCompany() {
        boolean unbanned = companyController.setBanStatus(testCompanyId, false);
        assertTrue(unbanned, "Unban should succeed");

        Company company = companyController.getCompany(testCompanyId);
        assertFalse(company.isBanned(), "Company should not be banned");
    }

    @Test
    @Order(10)
    @DisplayName("Test 10: Delete Company")
    public void testDeleteCompany() {
        boolean deleted = companyController.deleteCompany(testCompanyId);
        assertTrue(deleted, "Delete should succeed");

        Company company = companyController.getCompany(testCompanyId);
        assertNull(company, "Company should no longer exist");
    }
}