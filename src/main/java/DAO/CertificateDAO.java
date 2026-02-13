package DAO;

import Entities.Certificate;
import Entities.CertificateType;
import Entities.CertificateStatus;
import Utils.DatabasePlugin;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class CertificateDAO implements GenericDAO<Certificate, Long> {

    @Override
    public Certificate create(Certificate certificate) throws SQLException {
        String sql = "INSERT INTO certificates (company_id, type, certificate_number, " +
                "issue_date, expiry_date, status, country_of_origin, " +
                "issuing_authority, document_file) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setLong(1, certificate.getCompanyId());
            stmt.setString(2, certificate.getType().name());
            stmt.setString(3, certificate.getCertificateNumber());
            stmt.setTimestamp(4, Timestamp.valueOf(certificate.getIssueDate()));
            stmt.setTimestamp(5, Timestamp.valueOf(certificate.getExpiryDate()));
            stmt.setString(6, certificate.getStatus().name());
            stmt.setString(7, certificate.getCountryOfOrigin());
            stmt.setString(8, certificate.getIssuingAuthority());
            stmt.setString(9, certificate.getDocumentFile());

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    certificate.setId(rs.getLong(1));
                }
            }
        }

        return certificate;
    }

    @Override
    public Certificate findById(Long id) throws SQLException {
        String sql = "SELECT * FROM certificates WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToEntity(rs);
                }
            }
        }

        return null;
    }

    @Override
    public List<Certificate> findAll() throws SQLException {
        String sql = "SELECT * FROM certificates ORDER BY expiry_date DESC";
        List<Certificate> certificates = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                certificates.add(mapResultSetToEntity(rs));
            }
        }

        return certificates;
    }

    @Override
    public boolean update(Certificate certificate) throws SQLException {
        String sql = "UPDATE certificates SET company_id = ?, type = ?, certificate_number = ?, " +
                "issue_date = ?, expiry_date = ?, status = ?, country_of_origin = ?, " +
                "issuing_authority = ?, document_file = ? WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, certificate.getCompanyId());
            stmt.setString(2, certificate.getType().name());
            stmt.setString(3, certificate.getCertificateNumber());
            stmt.setTimestamp(4, Timestamp.valueOf(certificate.getIssueDate()));
            stmt.setTimestamp(5, Timestamp.valueOf(certificate.getExpiryDate()));
            stmt.setString(6, certificate.getStatus().name());
            stmt.setString(7, certificate.getCountryOfOrigin());
            stmt.setString(8, certificate.getIssuingAuthority());
            stmt.setString(9, certificate.getDocumentFile());
            stmt.setLong(10, certificate.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM certificates WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM certificates WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt(1) > 0;
                }
            }
        }

        return false;
    }

    @Override
    public long count() throws SQLException {
        String sql = "SELECT COUNT(*) FROM certificates";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }

        return 0;
    }

    // Custom methods
    public List<Certificate> findByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT * FROM certificates WHERE company_id = ? ORDER BY expiry_date DESC";
        List<Certificate> certificates = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    certificates.add(mapResultSetToEntity(rs));
                }
            }
        }

        return certificates;
    }

    public List<Certificate> findValidByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT * FROM certificates WHERE company_id = ? " +
                "AND status = 'VALID' AND expiry_date > NOW() ORDER BY expiry_date";
        List<Certificate> certificates = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    certificates.add(mapResultSetToEntity(rs));
                }
            }
        }

        return certificates;
    }

    public List<Certificate> findExpiringSoon(Long companyId, int daysBeforeExpiry) throws SQLException {
        String sql = "SELECT * FROM certificates WHERE company_id = ? " +
                "AND status = 'VALID' " +
                "AND expiry_date > NOW() " +
                "AND expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY) " +
                "ORDER BY expiry_date";
        List<Certificate> certificates = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);
            stmt.setInt(2, daysBeforeExpiry);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    certificates.add(mapResultSetToEntity(rs));
                }
            }
        }

        return certificates;
    }

    public Certificate findByCertificateNumber(String certificateNumber) throws SQLException {
        String sql = "SELECT * FROM certificates WHERE certificate_number = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, certificateNumber);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToEntity(rs);
                }
            }
        }

        return null;
    }

    private Certificate mapResultSetToEntity(ResultSet rs) throws SQLException {
        Certificate certificate = new Certificate();
        certificate.setId(rs.getLong("id"));
        certificate.setCompanyId(rs.getLong("company_id"));
        certificate.setType(CertificateType.valueOf(rs.getString("type")));
        certificate.setCertificateNumber(rs.getString("certificate_number"));

        Timestamp issueDate = rs.getTimestamp("issue_date");
        if (issueDate != null) {
            certificate.setIssueDate(issueDate.toLocalDateTime());
        }

        Timestamp expiryDate = rs.getTimestamp("expiry_date");
        if (expiryDate != null) {
            certificate.setExpiryDate(expiryDate.toLocalDateTime());
        }

        certificate.setStatus(CertificateStatus.valueOf(rs.getString("status")));
        certificate.setCountryOfOrigin(rs.getString("country_of_origin"));
        certificate.setIssuingAuthority(rs.getString("issuing_authority"));
        certificate.setDocumentFile(rs.getString("document_path"));

        return certificate;
    }
}