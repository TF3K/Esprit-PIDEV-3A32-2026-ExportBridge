package Services;

import DAO.CertificateDAO;
import Entities.Certificate;
import Entities.CertificateType;
import Entities.CertificateStatus;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.util.List;

public class CertificateService {
    private final CertificateDAO certificateDAO;

    public CertificateService() {
        this.certificateDAO = new CertificateDAO();
    }

    public Certificate createCertificate(Long companyId, CertificateType type,
                                         String certificateNumber, LocalDateTime issueDate,
                                         LocalDateTime expiryDate, String countryOfOrigin,
                                         String issuingAuthority) throws SQLException {

        if (companyId == null) {
            throw new IllegalArgumentException("Company ID is required");
        }

        if (type == null) {
            throw new IllegalArgumentException("Certificate type is required");
        }

        if (certificateNumber == null || certificateNumber.trim().isEmpty()) {
            throw new IllegalArgumentException("Certificate number is required");
        }

        if (issueDate == null || expiryDate == null) {
            throw new IllegalArgumentException("Issue date and expiry date are required");
        }

        if (expiryDate.isBefore(issueDate)) {
            throw new IllegalArgumentException("Expiry date cannot be before issue date");
        }

        Certificate existing = certificateDAO.findByCertificateNumber(certificateNumber);
        if (existing != null) {
            throw new IllegalArgumentException("Certificate number already exists");
        }

        Certificate certificate = new Certificate();
        certificate.setCompanyId(companyId);
        certificate.setType(type);
        certificate.setCertificateNumber(certificateNumber);
        certificate.setIssueDate(issueDate);
        certificate.setExpiryDate(expiryDate);
        certificate.setStatus(CertificateStatus.VALID);
        certificate.setCountryOfOrigin(countryOfOrigin);
        certificate.setIssuingAuthority(issuingAuthority);

        return certificateDAO.create(certificate);
    }

    public Certificate getCertificateById(Long id) throws SQLException {
        return certificateDAO.findById(id);
    }

    public List<Certificate> getCompanyCertificates(Long companyId) throws SQLException {
        return certificateDAO.findByCompanyId(companyId);
    }

    public List<Certificate> getValidCertificates(Long companyId) throws SQLException {
        return certificateDAO.findValidByCompanyId(companyId);
    }

    public List<Certificate> getExpiringSoonCertificates(Long companyId, int daysBeforeExpiry) throws SQLException {
        return certificateDAO.findExpiringSoon(companyId, daysBeforeExpiry);
    }

    public boolean updateCertificate(Certificate certificate) throws SQLException {
        if (certificate.getId() == null) {
            throw new IllegalArgumentException("Certificate ID is required for update");
        }

        if (certificate.getExpiryDate().isBefore(LocalDateTime.now())) {
            certificate.setStatus(CertificateStatus.EXPIRED);
        }

        return certificateDAO.update(certificate);
    }

    public boolean deleteCertificate(Long certificateId) throws SQLException {
        return certificateDAO.delete(certificateId);
    }

    public int updateExpiredCertificates(Long companyId) throws SQLException {
        List<Certificate> certificates = certificateDAO.findByCompanyId(companyId);
        int updated = 0;

        for (Certificate cert : certificates) {
            if (cert.getStatus() == CertificateStatus.VALID &&
                    cert.getExpiryDate().isBefore(LocalDateTime.now())) {
                cert.setStatus(CertificateStatus.EXPIRED);
                if (certificateDAO.update(cert)) {
                    updated++;
                }
            }
        }

        return updated;
    }

    public boolean verifyCertificate(Long certificateId) throws SQLException {
        Certificate certificate = certificateDAO.findById(certificateId);
        if (certificate == null) {
            return false;
        }

        return certificate.isValid();
    }
}