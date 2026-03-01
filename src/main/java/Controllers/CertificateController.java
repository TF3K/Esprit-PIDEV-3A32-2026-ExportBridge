package Controllers;

import Entities.Certificate;
import Entities.CertificateStatus;
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
     * Get all certificates
     */
    public List<Certificate> getAllCertificates() {
        try {
            return certificateService.getAllCertificates();
        } catch (SQLException e) {
            System.err.println("Error loading certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get certificates by company
     */
    public List<Certificate> getCertificatesByCompany(Long companyId) {
        try {
            return certificateService.getCertificatesByCompany(companyId);
        } catch (SQLException e) {
            System.err.println("Error loading company certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get certificate by ID
     */
    public Certificate getCertificateById(Long id) {
        try {
            return certificateService.getCertificateById(id);
        } catch (SQLException e) {
            System.err.println("Error loading certificate: " + e.getMessage());
            return null;
        }
    }

    /**
     * Create new certificate
     */
    public Certificate createCertificate(CertificateType type, String certificateNumber,
                                         String issuingAuthority, LocalDateTime issueDate,
                                         LocalDateTime expiryDate, String countryOfOrigin,
                                         Long companyId) {
        try {
            return certificateService.createCertificate(
                    type, certificateNumber, issuingAuthority,
                    issueDate, expiryDate, countryOfOrigin, companyId
            );
        } catch (SQLException e) {
            System.err.println("Error creating certificate: " + e.getMessage());
            return null;
        }
    }

    /**
     * Update certificate
     */
    public boolean updateCertificate(Certificate certificate) {
        try {
            return certificateService.updateCertificate(certificate);
        } catch (SQLException e) {
            System.err.println("Error updating certificate: " + e.getMessage());
            return false;
        }
    }

    /**
     * Delete certificate
     */
    public boolean deleteCertificate(Long id) {
        try {
            return certificateService.deleteCertificate(id);
        } catch (SQLException e) {
            System.err.println("Error deleting certificate: " + e.getMessage());
            return false;
        }
    }

    /**
     * Get active/valid certificates
     */
    public List<Certificate> getActiveCertificates() {
        try {
            return certificateService.getActiveCertificates();
        } catch (SQLException e) {
            System.err.println("Error loading active certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get expiring certificates (within 30 days)
     */
    public List<Certificate> getExpiringCertificates() {
        try {
            return certificateService.getExpiringCertificates(30);
        } catch (SQLException e) {
            System.err.println("Error loading expiring certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get expired certificates
     */
    public List<Certificate> getExpiredCertificates() {
        try {
            return certificateService.getExpiredCertificates();
        } catch (SQLException e) {
            System.err.println("Error loading expired certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Get pending certificates
     */
    public List<Certificate> getPendingCertificates() {
        try {
            return certificateService.getPendingCertificates();
        } catch (SQLException e) {
            System.err.println("Error loading pending certificates: " + e.getMessage());
            return List.of();
        }
    }

    /**
     * Renew certificate (extend expiry date)
     */
    public boolean renewCertificate(Long certificateId, LocalDateTime newExpiryDate) {
        try {
            return certificateService.renewCertificate(certificateId, newExpiryDate);
        } catch (SQLException e) {
            System.err.println("Error renewing certificate: " + e.getMessage());
            return false;
        }
    }

    /**
     * Approve pending certificate
     */
    public boolean approveCertificate(Long certificateId) {
        try {
            return certificateService.approveCertificate(certificateId);
        } catch (SQLException e) {
            System.err.println("Error approving certificate: " + e.getMessage());
            return false;
        }
    }

    /**
     * Reject pending certificate
     */
    public boolean rejectCertificate(Long certificateId) {
        try {
            return certificateService.rejectCertificate(certificateId);
        } catch (SQLException e) {
            System.err.println("Error rejecting certificate: " + e.getMessage());
            return false;
        }
    }

    /**
     * Revoke certificate
     */
    public boolean revokeCertificate(Long certificateId) {
        try {
            return certificateService.revokeCertificate(certificateId);
        } catch (SQLException e) {
            System.err.println("Error revoking certificate: " + e.getMessage());
            return false;
        }
    }
}