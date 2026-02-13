package Controllers;

import Entities.Certificate;
import Entities.CertificateType;
import Services.CertificateService;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.util.List;

public class CertificateController {
    private final CertificateService certificateService;

    public CertificateController() {
        this.certificateService = new CertificateService();
    }

    /**
     * Create certificate
     */
    public Certificate createCertificate(Long companyId, CertificateType type,
                                         String certificateNumber, LocalDateTime issueDate,
                                         LocalDateTime expiryDate, String countryOfOrigin,
                                         String issuingAuthority) {
        try {
            return certificateService.createCertificate(companyId, type, certificateNumber,
                    issueDate, expiryDate, countryOfOrigin,
                    issuingAuthority);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return null;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return null;
        }
    }

    /**
     * Get certificate by ID
     */
    public Certificate getCertificate(Long id) {
        try {
            return certificateService.getCertificateById(id);
        } catch (SQLException e) {
            System.err.println("Error fetching certificate: " + e.getMessage());
            return null;
        }
    }

    /**
     * Get all certificates for a company
     */
    public List<Certificate> getCompanyCertificates(Long companyId) {
        try {
            return certificateService.getCompanyCertificates(companyId);
        } catch (SQLException e) {
            System.err.println("Error fetching certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get valid certificates
     */
    public List<Certificate> getValidCertificates(Long companyId) {
        try {
            return certificateService.getValidCertificates(companyId);
        } catch (SQLException e) {
            System.err.println("Error fetching valid certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get expiring soon certificates
     */
    public List<Certificate> getExpiringSoon(Long companyId, int daysBeforeExpiry) {
        try {
            return certificateService.getExpiringSoonCertificates(companyId, daysBeforeExpiry);
        } catch (SQLException e) {
            System.err.println("Error fetching expiring certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Update certificate
     */
    public boolean updateCertificate(Certificate certificate) {
        try {
            return certificateService.updateCertificate(certificate);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    /**
     * Delete certificate
     */
    public boolean deleteCertificate(Long certificateId) {
        try {
            return certificateService.deleteCertificate(certificateId);
        } catch (SQLException e) {
            System.err.println("Error deleting certificate: " + e.getMessage());
            return false;
        }
    }

    /**
     * Verify certificate validity
     */
    public boolean verifyCertificate(Long certificateId) {
        try {
            return certificateService.verifyCertificate(certificateId);
        } catch (SQLException e) {
            System.err.println("Error verifying certificate: " + e.getMessage());
            return false;
        }
    }
}