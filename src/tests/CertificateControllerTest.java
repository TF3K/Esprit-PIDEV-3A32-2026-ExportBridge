import Controllers.CertificateController;
import Controllers.CompanyController;
import Entities.Certificate;
import Entities.CertificateType;
import Entities.CertificateStatus;
import org.junit.jupiter.api.*;

import java.time.LocalDateTime;
import java.util.List;

import static org.junit.jupiter.api.Assertions.*;

@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class CertificateControllerTest {

    private static CertificateController certificateController;
    private static CompanyController companyController;
    private static Long testCompanyId;
    private static Long testCertificateId;

    @BeforeAll
    public static void setup() {
        certificateController = new CertificateController();
        companyController = new CompanyController();

        var company = companyController.createCompany(
                "Certificate Test Company",
                "TAX_CERT_" + System.currentTimeMillis(),
                "REG_CERT_123",
                "Test Address",
                "cert@test.tn",
                "+216 22 222 222",
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
    @DisplayName("Test 1: Create New Certificate")
    public void testCreateCertificate() {
        Certificate certificate = certificateController.createCertificate(
                testCompanyId,
                CertificateType.EUR1_ORIGIN,
                "EUR1-2024-" + System.currentTimeMillis(),
                LocalDateTime.now(),
                LocalDateTime.now().plusYears(1),
                "Tunisia",
                "Tunisian Customs Authority"
        );

        assertNotNull(certificate, "Certificate creation should succeed");
        assertNotNull(certificate.getId(), "Certificate should have an ID");
        assertEquals(CertificateType.EUR1_ORIGIN, certificate.getType());
        assertEquals(CertificateStatus.VALID, certificate.getStatus());
        assertTrue(certificate.isValid(), "Certificate should be valid");

        testCertificateId = certificate.getId();
    }

    @Test
    @Order(2)
    @DisplayName("Test 2: Create Duplicate Certificate Number")
    public void testCreateDuplicateCertificate() {
        Certificate existing = certificateController.getCertificate(testCertificateId);

        Certificate duplicate = certificateController.createCertificate(
                testCompanyId,
                CertificateType.CE_CONFORMITY,
                existing.getCertificateNumber(),
                LocalDateTime.now(),
                LocalDateTime.now().plusYears(1),
                "Tunisia",
                "Another Authority"
        );

        assertNull(duplicate, "Should not create duplicate certificate number");
    }

    @Test
    @Order(3)
    @DisplayName("Test 3: Get Certificate by ID")
    public void testGetCertificate() {
        Certificate certificate = certificateController.getCertificate(testCertificateId);

        assertNotNull(certificate, "Should retrieve certificate");
        assertEquals(testCertificateId, certificate.getId());
        assertEquals("Tunisia", certificate.getCountryOfOrigin());
    }

    @Test
    @Order(4)
    @DisplayName("Test 4: Get Company Certificates")
    public void testGetCompanyCertificates() {
        List<Certificate> certificates = certificateController.getCompanyCertificates(testCompanyId);

        assertNotNull(certificates, "Should return list");
        assertFalse(certificates.isEmpty(), "Should have at least one certificate");
        assertTrue(certificates.stream().anyMatch(c -> c.getId().equals(testCertificateId)),
                "Should contain our test certificate");
    }

    @Test
    @Order(5)
    @DisplayName("Test 5: Get Valid Certificates")
    public void testGetValidCertificates() {
        List<Certificate> validCerts = certificateController.getValidCertificates(testCompanyId);

        assertNotNull(validCerts, "Should return list");
        assertFalse(validCerts.isEmpty(), "Should have valid certificates");
        assertTrue(validCerts.stream().allMatch(Certificate::isValid),
                "All certificates should be valid");
    }

    @Test
    @Order(6)
    @DisplayName("Test 6: Create Expiring Soon Certificate")
    public void testExpiringSoonCertificate() {
        Certificate expiringSoon = certificateController.createCertificate(
                testCompanyId,
                CertificateType.HEALTH_CERTIFICATE,
                "HEALTH-2024-" + System.currentTimeMillis(),
                LocalDateTime.now(),
                LocalDateTime.now().plusDays(15),
                "Tunisia",
                "Ministry of Health"
        );

        assertNotNull(expiringSoon);
        assertTrue(expiringSoon.isExpiringSoon(), "Should be expiring soon");

        List<Certificate> expiring = certificateController.getExpiringSoon(testCompanyId, 30);
        assertTrue(expiring.stream().anyMatch(c -> c.getId().equals(expiringSoon.getId())),
                "Should find expiring certificate");
    }

    @Test
    @Order(7)
    @DisplayName("Test 7: Update Certificate")
    public void testUpdateCertificate() {
        Certificate certificate = certificateController.getCertificate(testCertificateId);
        assertNotNull(certificate);

        certificate.setIssuingAuthority("Updated Authority");
        certificate.setExpiryDate(LocalDateTime.now().plusYears(2));

        boolean updated = certificateController.updateCertificate(certificate);
        assertTrue(updated, "Update should succeed");

        Certificate updatedCert = certificateController.getCertificate(testCertificateId);
        assertEquals("Updated Authority", updatedCert.getIssuingAuthority());
    }

    @Test
    @Order(8)
    @DisplayName("Test 8: Verify Certificate Validity")
    public void testVerifyCertificate() {
        boolean isValid = certificateController.verifyCertificate(testCertificateId);
        assertTrue(isValid, "Certificate should be valid");
    }

    @Test
    @Order(9)
    @DisplayName("Test 9: Create Multiple Certificate Types")
    public void testMultipleCertificateTypes() {
        Certificate ce = certificateController.createCertificate(
                testCompanyId,
                CertificateType.CE_CONFORMITY,
                "CE-2024-" + System.currentTimeMillis(),
                LocalDateTime.now(),
                LocalDateTime.now().plusYears(3),
                "Tunisia",
                "CE Authority"
        );

        Certificate iso = certificateController.createCertificate(
                testCompanyId,
                CertificateType.ISO_22000,
                "ISO-2024-" + System.currentTimeMillis(),
                LocalDateTime.now(),
                LocalDateTime.now().plusYears(3),
                "Tunisia",
                "ISO Certification Body"
        );

        assertNotNull(ce);
        assertNotNull(iso);

        List<Certificate> all = certificateController.getCompanyCertificates(testCompanyId);
        assertTrue(all.size() >= 4, "Should have multiple certificate types");
    }

    @Test
    @Order(10)
    @DisplayName("Test 10: Delete Certificate")
    public void testDeleteCertificate() {
        boolean deleted = certificateController.deleteCertificate(testCertificateId);
        assertTrue(deleted, "Delete should succeed");

        Certificate certificate = certificateController.getCertificate(testCertificateId);
        assertNull(certificate, "Certificate should no longer exist");
    }
}