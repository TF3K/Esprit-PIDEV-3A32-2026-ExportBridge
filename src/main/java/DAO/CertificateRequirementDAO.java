package DAO;

import Entities.CertificateRequirement;
import Entities.CertificateType;
import Entities.ProductCategory;
import Utils.DatabasePlugin;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class CertificateRequirementDAO implements GenericDAO<CertificateRequirement, Long> {

    @Override
    public CertificateRequirement create(CertificateRequirement requirement) throws SQLException {
        String sql = "INSERT INTO certificate_requirements (market_id, product_category, " +
                "certificate_type, mandatory, description) VALUES (?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setLong(1, requirement.getMarketId());
            stmt.setString(2, requirement.getProductCategory().name());
            stmt.setString(3, requirement.getCertificateType().name());
            stmt.setBoolean(4, requirement.isMandatory());
            stmt.setString(5, requirement.getDescription());

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    requirement.setId(rs.getLong(1));
                }
            }
        }

        return requirement;
    }

    @Override
    public CertificateRequirement findById(Long id) throws SQLException {
        String sql = "SELECT * FROM certificate_requirements WHERE id = ?";

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
    public List<CertificateRequirement> findAll() throws SQLException {
        String sql = "SELECT * FROM certificate_requirements ORDER BY market_id, product_category";
        List<CertificateRequirement> requirements = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                requirements.add(mapResultSetToEntity(rs));
            }
        }

        return requirements;
    }

    @Override
    public boolean update(CertificateRequirement requirement) throws SQLException {
        String sql = "UPDATE certificate_requirements SET market_id = ?, product_category = ?, " +
                "certificate_type = ?, mandatory = ?, description = ? WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, requirement.getMarketId());
            stmt.setString(2, requirement.getProductCategory().name());
            stmt.setString(3, requirement.getCertificateType().name());
            stmt.setBoolean(4, requirement.isMandatory());
            stmt.setString(5, requirement.getDescription());
            stmt.setLong(6, requirement.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM certificate_requirements WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM certificate_requirements WHERE id = ?";

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
        String sql = "SELECT COUNT(*) FROM certificate_requirements";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }

        return 0;
    }

    public List<CertificateRequirement> findByMarketId(Long marketId) throws SQLException {
        String sql = "SELECT * FROM certificate_requirements WHERE market_id = ? " +
                "ORDER BY product_category, mandatory DESC";
        List<CertificateRequirement> requirements = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, marketId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    requirements.add(mapResultSetToEntity(rs));
                }
            }
        }

        return requirements;
    }

    public List<CertificateRequirement> findByMarketAndCategory(Long marketId,
                                                                ProductCategory category)
            throws SQLException {
        String sql = "SELECT * FROM certificate_requirements " +
                "WHERE market_id = ? AND product_category = ? " +
                "ORDER BY mandatory DESC";
        List<CertificateRequirement> requirements = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, marketId);
            stmt.setString(2, category.name());

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    requirements.add(mapResultSetToEntity(rs));
                }
            }
        }

        return requirements;
    }
    
    public List<CertificateRequirement> findMandatoryByMarket(Long marketId) throws SQLException {
        String sql = "SELECT * FROM certificate_requirements " +
                "WHERE market_id = ? AND mandatory = true " +
                "ORDER BY product_category";
        List<CertificateRequirement> requirements = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, marketId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    requirements.add(mapResultSetToEntity(rs));
                }
            }
        }

        return requirements;
    }
    
    public List<CertificateRequirement> findByProductCategory(ProductCategory category)
            throws SQLException {
        String sql = "SELECT * FROM certificate_requirements WHERE product_category = ? " +
                "ORDER BY market_id, mandatory DESC";
        List<CertificateRequirement> requirements = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, category.name());

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    requirements.add(mapResultSetToEntity(rs));
                }
            }
        }

        return requirements;
    }

    public List<CertificateRequirement> findByCertificateType(CertificateType type)
            throws SQLException {
        String sql = "SELECT * FROM certificate_requirements WHERE certificate_type = ? " +
                "ORDER BY market_id, product_category";
        List<CertificateRequirement> requirements = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, type.name());

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    requirements.add(mapResultSetToEntity(rs));
                }
            }
        }

        return requirements;
    }
    
    public boolean deleteByMarketId(Long marketId) throws SQLException {
        String sql = "DELETE FROM certificate_requirements WHERE market_id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, marketId);
            return stmt.executeUpdate() > 0;
        }
    }
    
    private CertificateRequirement mapResultSetToEntity(ResultSet rs) throws SQLException {
        CertificateRequirement requirement = new CertificateRequirement();
        requirement.setId(rs.getLong("id"));
        requirement.setMarketId(rs.getLong("market_id"));
        requirement.setProductCategory(ProductCategory.valueOf(rs.getString("product_category")));
        requirement.setCertificateType(CertificateType.valueOf(rs.getString("certificate_type")));
        requirement.setMandatory(rs.getBoolean("mandatory"));
        requirement.setDescription(rs.getString("description"));
        return requirement;
    }
}