package DAO;

import Entities.Company;
import Utils.DatabasePlugin;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class CompanyDAO implements GenericDAO<Company, Long> {

    @Override
    public Company create(Company company) throws SQLException {
        String sql = "INSERT INTO companies (company_name, domain, tax_number, registration_number, " +
                "country, address, contact_email, contact_phone, rating, warnings, is_banned, " +
                "company_manager_id, created_at, last_updated) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setString(1, company.getCompanyName());
            stmt.setString(2, company.getDomain());
            stmt.setString(3, company.getTaxNumber());
            stmt.setString(4, company.getRegistrationNumber());
            stmt.setString(5, company.getCountry());
            stmt.setString(6, company.getAddress());
            stmt.setString(7, company.getContactEmail());
            stmt.setString(8, company.getContactPhone());

            if (company.getRating() != null) {
                stmt.setInt(9, company.getRating());
            } else {
                stmt.setNull(9, Types.INTEGER);
            }

            if (company.getWarnings() != null) {
                stmt.setInt(10, company.getWarnings());
            } else {
                stmt.setInt(10, 0);
            }

            stmt.setBoolean(11, company.isBanned());

            if (company.getCompanyManagerId() != null) {
                stmt.setLong(12, company.getCompanyManagerId());
            } else {
                stmt.setNull(12, Types.BIGINT);
            }

            LocalDateTime created = company.getCreatedAt() != null ? company.getCreatedAt() : LocalDateTime.now();
            LocalDateTime updated = company.getLastUpdated() != null ? company.getLastUpdated() : LocalDateTime.now();

            company.setCreatedAt(created);
            company.setLastUpdated(updated);

            stmt.setTimestamp(13, Timestamp.valueOf(created));
            stmt.setTimestamp(14, Timestamp.valueOf(updated));

            int affectedRows = stmt.executeUpdate();

            if (affectedRows == 0) {
                throw new SQLException("Creating company failed, no rows affected.");
            }

            try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    company.setId(generatedKeys.getLong(1));
                } else {
                    throw new SQLException("Creating company failed, no ID obtained.");
                }
            }
        }
        return company;
    }

    @Override
    public Company findById(Long id) throws SQLException {
        String sql = "SELECT * FROM companies WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToCompany(rs);
                }
            }
        }
        return null;
    }

    @Override
    public List<Company> findAll() throws SQLException {
        String sql = "SELECT * FROM companies";
        List<Company> companies = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                companies.add(mapResultSetToCompany(rs));
            }
        }
        return companies;
    }

    public Company findByTaxNumber(String taxNumber) throws SQLException {
        String sql = "SELECT * FROM companies WHERE tax_number = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, taxNumber);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToCompany(rs);
                }
            }
        }
        return null;
    }

    @Override
    public boolean update(Company company) throws SQLException {
        String sql = "UPDATE companies SET company_name = ?, domain = ?, tax_number = ?, " +
                "registration_number = ?, country = ?, address = ?, contact_email = ?, " +
                "contact_phone = ?, rating = ?, warnings = ?, is_banned = ?, " +
                "company_manager_id = ?, last_updated = ? " +
                "WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, company.getCompanyName());
            stmt.setString(2, company.getDomain());
            stmt.setString(3, company.getTaxNumber());
            stmt.setString(4, company.getRegistrationNumber());
            stmt.setString(5, company.getCountry());
            stmt.setString(6, company.getAddress());
            stmt.setString(7, company.getContactEmail());
            stmt.setString(8, company.getContactPhone());

            if (company.getRating() != null) {
                stmt.setInt(9, company.getRating());
            } else {
                stmt.setNull(9, Types.INTEGER);
            }

            if (company.getWarnings() != null) {
                stmt.setInt(10, company.getWarnings());
            } else {
                stmt.setInt(10, 0);
            }

            stmt.setBoolean(11, company.isBanned());

            if (company.getCompanyManagerId() != null) {
                stmt.setLong(12, company.getCompanyManagerId());
            } else {
                stmt.setNull(12, Types.BIGINT);
            }

            stmt.setTimestamp(13, Timestamp.valueOf(LocalDateTime.now()));

            stmt.setLong(14, company.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM companies WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM companies WHERE id = ?";

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
        String sql = "SELECT COUNT(*) FROM companies";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }
        return 0;
    }

    private Company mapResultSetToCompany(ResultSet rs) throws SQLException {
        Company company = new Company();
        company.setId(rs.getLong("id"));
        company.setCompanyName(rs.getString("company_name"));
        company.setDomain(rs.getString("domain"));
        company.setTaxNumber(rs.getString("tax_number"));
        company.setRegistrationNumber(rs.getString("registration_number"));
        company.setCountry(rs.getString("country"));
        company.setAddress(rs.getString("address"));
        company.setContactEmail(rs.getString("contact_email"));
        company.setContactPhone(rs.getString("contact_phone"));

        int rating = rs.getInt("rating");
        if (!rs.wasNull()) {
            company.setRating(rating);
        }

        int warnings = rs.getInt("warnings");
        if (!rs.wasNull()) {
            company.setWarnings(warnings);
        }

        company.setBanned(rs.getBoolean("is_banned"));

        long managerId = rs.getLong("company_manager_id");
        if (!rs.wasNull()) {
            company.setCompanyManagerId(managerId);
        }

        Timestamp createdAt = rs.getTimestamp("created_at");
        if (createdAt != null) {
            company.setCreatedAt(createdAt.toLocalDateTime());
        }

        Timestamp lastUpdated = rs.getTimestamp("last_updated");
        if (lastUpdated != null) {
            company.setLastUpdated(lastUpdated.toLocalDateTime());
        }

        return company;
    }
}