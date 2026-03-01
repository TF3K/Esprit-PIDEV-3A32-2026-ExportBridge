package Services;

import DAO.CertificateDAO;
import Entities.Certificate;
import Entities.CertificateStatus;
import Entities.CertificateType;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.List;
import java.util.stream.Collectors;

public class CertificateService {

    private final CertificateDAO certificateDAO;

    public CertificateService() {
        this.certificateDAO = new CertificateDAO();
    }

    /**
     * Get all certificates
     */
    public List<Certificate> getAllCertificates() throws SQLException {
        List<Certificate> certificates = certificateDAO.findAll();

        // Update statuses based on expiry dates
        certificates.forEach(this::updateCertificateStatus);

        return certificates;
    }

    /**
     * Get certificate by ID
     */
    public Certificate getCertificateById(Long id) throws SQLException {
        Certificate cert = certificateDAO.findById(id);
        if (cert != null) {
            updateCertificateStatus(cert);
        }
        return cert;
    }

    /**
     * Get certificates by company ID
     */
    public List<Certificate> getCertificatesByCompany(Long companyId) throws SQLException {
        List<Certificate> certificates = certificateDAO.findByCompanyId(companyId);
        certificates.forEach(this::updateCertificateStatus);
        return certificates;
    }

    /**
     * Get active/valid certificates
     */
    public List<Certificate> getActiveCertificates() throws SQLException {
        List<Certificate> all = getAllCertificates();
        return all.stream()
                .filter(c -> c.getStatus() == CertificateStatus.VALID)
                .filter(Certificate::isValid)
                .collect(Collectors.toList());
    }

    /**
     * Get expired certificates
     */
    public List<Certificate> getExpiredCertificates() throws SQLException {
        List<Certificate> all = getAllCertificates();
        return all.stream()
                .filter(c -> c.getStatus() == CertificateStatus.EXPIRED)
                .collect(Collectors.toList());
    }

    /**
     * Get certificates expiring within specified days
     */
    public List<Certificate> getExpiringCertificates(int daysThreshold) throws SQLException {
        List<Certificate> all = getAllCertificates();
        LocalDateTime now = LocalDateTime.now();

        return all.stream()
                .filter(c -> c.getStatus() == CertificateStatus.VALID)
                .filter(c -> c.getExpiryDate() != null)
                .filter(c -> {
                    long daysUntilExpiry = ChronoUnit.DAYS.between(now, c.getExpiryDate());
                    return daysUntilExpiry >= 0 && daysUntilExpiry <= daysThreshold;
                })
                .collect(Collectors.toList());
    }

    /**
     * Create new certificate
     */
    public Certificate createCertificate(CertificateType type, String certificateNumber,
                                         String issuingAuthority, LocalDateTime issueDate,
                                         LocalDateTime expiryDate, String countryOfOrigin,
                                         Long companyId) throws SQLException {

        Certificate certificate = Certificate.builder()
                .type(type)
                .certificateNumber(certificateNumber)
                .issuingAuthority(issuingAuthority)
                .issueDate(issueDate)
                .expiryDate(expiryDate)
                .countryOfOrigin(countryOfOrigin)
                .companyId(companyId)
                .status(determineStatus(expiryDate))
                .build();

        Certificate created = certificateDAO.create(certificate);

        if (created != null) {
            System.out.println("✓ Certificate created: " + type.name() + " #" + certificateNumber);
        }

        return created;
    }

    /**
     * Update certificate
     */
    public boolean updateCertificate(Certificate certificate) throws SQLException {
        // Update status based on expiry date
        updateCertificateStatus(certificate);

        boolean success = certificateDAO.update(certificate);

        if (success) {
            System.out.println("✓ Certificate updated: " + certificate.getType().name());
        }

        return success;
    }

    /**
     * Delete certificate
     */
    public boolean deleteCertificate(Long id) throws SQLException {
        boolean success = certificateDAO.delete(id);

        if (success) {
            System.out.println("✓ Certificate deleted: ID " + id);
        }

        return success;
    }

    /**
     * Renew certificate with new expiry date
     */
    public boolean renewCertificate(Long certificateId, LocalDateTime newExpiryDate) throws SQLException {
        Certificate cert = certificateDAO.findById(certificateId);

        if (cert == null) {
            System.err.println("✗ Certificate not found: " + certificateId);
            return false;
        }

        cert.setExpiryDate(newExpiryDate);
        cert.setStatus(CertificateStatus.VALID);
        cert.setIssueDate(LocalDateTime.now()); // Update issue date to now

        boolean success = certificateDAO.update(cert);

        if (success) {
            System.out.println("✓ Certificate renewed: " + cert.getType().name() +
                    " - New expiry: " + newExpiryDate);
        }

        return success;
    }

    /**
     * Get days until certificate expires
     */
    public long getDaysUntilExpiry(Certificate certificate) {
        if (certificate.getExpiryDate() == null) return -1;
        return ChronoUnit.DAYS.between(LocalDateTime.now(), certificate.getExpiryDate());
    }

    /**
     * Update certificate status based on expiry date
     */
    private void updateCertificateStatus(Certificate certificate) {
        if (certificate.getExpiryDate() == null) {
            certificate.setStatus(CertificateStatus.VALID);
            return;
        }

        LocalDateTime now = LocalDateTime.now();

        if (certificate.getExpiryDate().isBefore(now)) {
            certificate.setStatus(CertificateStatus.EXPIRED);
        } else {
            // Keep as VALID or PENDING based on current status
            if (certificate.getStatus() == CertificateStatus.EXPIRED) {
                certificate.setStatus(CertificateStatus.VALID);
            }
        }
    }

    /**
     * Determine certificate status based on expiry date
     */
    private CertificateStatus determineStatus(LocalDateTime expiryDate) {
        if (expiryDate == null) {
            return CertificateStatus.VALID;
        }

        LocalDateTime now = LocalDateTime.now();

        if (expiryDate.isBefore(now)) {
            return CertificateStatus.EXPIRED;
        } else {
            return CertificateStatus.VALID;
        }
    }

    /**
     * Get certificate count by status
     */
    public int getCountByStatus(CertificateStatus status) throws SQLException {
        List<Certificate> all = getAllCertificates();
        return (int) all.stream()
                .filter(c -> c.getStatus() == status)
                .count();
    }

    /**
     * Get certificates by type
     */
    public List<Certificate> getCertificatesByType(CertificateType type) throws SQLException {
        List<Certificate> all = getAllCertificates();
        return all.stream()
                .filter(c -> c.getType() == type)
                .collect(Collectors.toList());
    }

    /**
     * Validate certificate before creation/update
     */
    public boolean validateCertificate(Certificate certificate) {
        if (certificate.getType() == null) {
            System.err.println("✗ Certificate type is required");
            return false;
        }

        if (certificate.getCertificateNumber() == null || certificate.getCertificateNumber().trim().isEmpty()) {
            System.err.println("✗ Certificate number is required");
            return false;
        }

        if (certificate.getIssueDate() != null && certificate.getExpiryDate() != null) {
            if (certificate.getExpiryDate().isBefore(certificate.getIssueDate())) {
                System.err.println("✗ Expiry date cannot be before issue date");
                return false;
            }
        }

        return true;
    }

    /**
     * Get all unique certificate types in use
     */
    public List<CertificateType> getCertificateTypesInUse() throws SQLException {
        List<Certificate> all = certificateDAO.findAll();
        return all.stream()
                .map(Certificate::getType)
                .distinct()
                .sorted()
                .collect(Collectors.toList());
    }

    /**
     * Get certificates requiring renewal (expiring in 30 days or less)
     */
    public List<Certificate> getCertificatesRequiringRenewal() throws SQLException {
        return getExpiringCertificates(30);
    }

    /**
     * Get certificates by country of origin
     */
    public List<Certificate> getCertificatesByCountry(String countryOfOrigin) throws SQLException {
        List<Certificate> all = getAllCertificates();
        return all.stream()
                .filter(c -> c.getCountryOfOrigin() != null &&
                        c.getCountryOfOrigin().equalsIgnoreCase(countryOfOrigin))
                .collect(Collectors.toList());
    }

    /**
     * Get pending certificates (awaiting approval)
     */
    public List<Certificate> getPendingCertificates() throws SQLException {
        List<Certificate> all = getAllCertificates();
        return all.stream()
                .filter(c -> c.getStatus() == CertificateStatus.PENDING)
                .collect(Collectors.toList());
    }

    /**
     * Approve pending certificate
     */
    public boolean approveCertificate(Long certificateId) throws SQLException {
        Certificate cert = certificateDAO.findById(certificateId);

        if (cert == null || cert.getStatus() != CertificateStatus.PENDING) {
            return false;
        }

        cert.setStatus(CertificateStatus.VALID);
        return certificateDAO.update(cert);
    }

    /**
     * Reject pending certificate
     */
    public boolean rejectCertificate(Long certificateId) throws SQLException {
        Certificate cert = certificateDAO.findById(certificateId);

        if (cert == null || cert.getStatus() != CertificateStatus.PENDING) {
            return false;
        }

        cert.setStatus(CertificateStatus.REJECTED);
        return certificateDAO.update(cert);
    }

    /**
     * Revoke certificate
     */
    public boolean revokeCertificate(Long certificateId) throws SQLException {
        Certificate cert = certificateDAO.findById(certificateId);

        if (cert == null) {
            return false;
        }

        cert.setStatus(CertificateStatus.REVOKED);
        return certificateDAO.update(cert);
    }
}