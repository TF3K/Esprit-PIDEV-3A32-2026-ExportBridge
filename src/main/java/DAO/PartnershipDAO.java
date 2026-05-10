package DAO;

import Entities.Partnership;
import Entities.PartnershipStatus;
import Entities.PartnershipType;
import Utils.DatabasePlugin;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class PartnershipDAO implements GenericDAO<Partnership, Long> {

    @Override
    public Partnership create(Partnership partnership) throws SQLException {
        String sql = "INSERT INTO partnerships (source_company_id, status, type, " +
                "established_date, terminated_date, notes) " +
                "VALUES (?, ?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setLong(1, partnership.getSourceCompanyId());
            stmt.setString(2, partnership.getStatus().name());

            if (partnership.getType() != null) {
                stmt.setString(3, partnership.getType().name());
            } else {
                stmt.setNull(3, Types.VARCHAR);
            }

            if (partnership.getEstablishedDate() != null) {
                stmt.setDate(4, Date.valueOf(partnership.getEstablishedDate().toLocalDate()));
            } else {
                stmt.setNull(4, Types.DATE);
            }

            if (partnership.getTerminatedDate() != null) {
                stmt.setDate(5, Date.valueOf(partnership.getTerminatedDate().toLocalDate()));
            } else {
                stmt.setNull(5, Types.DATE);
            }

            stmt.setString(6, partnership.getNotes());

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    partnership.setId(rs.getLong(1));
                }
            }

            // Fetch timestamps
            Partnership created = findById(partnership.getId());
            if (created != null) {
                partnership.setCreatedAt(created.getCreatedAt());
                partnership.setLastUpdated(created.getLastUpdated());
            }
        }

        return partnership;
    }

    @Override
    public Partnership findById(Long id) throws SQLException {
        String sql = "SELECT * FROM partnerships WHERE id = ?";

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
    public List<Partnership> findAll() throws SQLException {
        String sql = "SELECT * FROM partnerships ORDER BY created_at DESC";
        List<Partnership> partnerships = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                partnerships.add(mapResultSetToEntity(rs));
            }
        }

        return partnerships;
    }

    @Override
    public boolean update(Partnership partnership) throws SQLException {
        String sql = "UPDATE partnerships SET source_company_id = ?, " +
                "status = ?, type = ?, established_date = ?, terminated_date = ?, notes = ? " +
                "WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, partnership.getSourceCompanyId());
            stmt.setString(2, partnership.getStatus().name());

            if (partnership.getType() != null) {
                stmt.setString(3, partnership.getType().name());
            } else {
                stmt.setNull(3, Types.VARCHAR);
            }

            if (partnership.getEstablishedDate() != null) {
                stmt.setDate(4, Date.valueOf(partnership.getEstablishedDate().toLocalDate()));
            } else {
                stmt.setNull(4, Types.DATE);
            }

            if (partnership.getTerminatedDate() != null) {
                stmt.setDate(5, Date.valueOf(partnership.getTerminatedDate().toLocalDate()));
            } else {
                stmt.setNull(5, Types.DATE);
            }

            stmt.setString(6, partnership.getNotes());
            stmt.setLong(7, partnership.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM partnerships WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM partnerships WHERE id = ?";

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
        String sql = "SELECT COUNT(*) FROM partnerships";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql);
                ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }

        return 0;
    }

    public List<Partnership> findByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT * FROM partnerships " +
                "WHERE source_company_id = ? " +
                "ORDER BY created_at DESC";
        List<Partnership> partnerships = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    partnerships.add(mapResultSetToEntity(rs));
                }
            }
        }

        return partnerships;
    }

    public List<Partnership> findByStatus(PartnershipStatus status) throws SQLException {
        String sql = "SELECT * FROM partnerships WHERE status = ? ORDER BY created_at DESC";
        List<Partnership> partnerships = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, status.name());

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    partnerships.add(mapResultSetToEntity(rs));
                }
            }
        }

        return partnerships;
    }

    public List<Partnership> findActiveByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT * FROM partnerships " +
                "WHERE source_company_id = ? " +
                "AND status = 'ACTIVE' " +
                "AND (terminated_date IS NULL OR terminated_date > NOW()) " +
                "ORDER BY established_date DESC";
        List<Partnership> partnerships = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    partnerships.add(mapResultSetToEntity(rs));
                }
            }
        }

        return partnerships;
    }

    public Partnership findByCompanies(Long sourceId, Long targetId) throws SQLException {
        // With single-company relationship, a partnership is tied to one company only.
        // We treat this as finding any partnership where source_company_id equals
        // either id.
        String sql = "SELECT * FROM partnerships " +
                "WHERE source_company_id = ? " +
                "ORDER BY created_at DESC LIMIT 1";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, sourceId);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToEntity(rs);
                }
            }
        }

        return null;
    }

    public long countByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT COUNT(*) FROM partnerships " +
                "WHERE source_company_id = ? OR target_company_id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
                PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, companyId);
            stmt.setLong(2, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getLong(1);
                }
            }
        }

        return 0;
    }

    private Partnership mapResultSetToEntity(ResultSet rs) throws SQLException {
        Partnership partnership = new Partnership();
        partnership.setId(rs.getLong("id"));
        partnership.setSourceCompanyId(rs.getLong("source_company_id"));
        partnership.setStatus(PartnershipStatus.valueOf(rs.getString("status")));

        String typeStr = rs.getString("type");
        if (typeStr != null && !rs.wasNull()) {
            partnership.setType(PartnershipType.valueOf(typeStr));
        }

        Date establishedDate = rs.getDate("established_date");
        if (establishedDate != null) {
            partnership.setEstablishedDate(establishedDate.toLocalDate().atStartOfDay());
        }

        Date terminatedDate = rs.getDate("terminated_date");
        if (terminatedDate != null) {
            partnership.setTerminatedDate(terminatedDate.toLocalDate().atStartOfDay());
        }

        partnership.setNotes(rs.getString("notes"));

        Timestamp createdAt = rs.getTimestamp("created_at");
        if (createdAt != null) {
            partnership.setCreatedAt(createdAt.toLocalDateTime());
        }

        Timestamp lastUpdated = rs.getTimestamp("last_updated");
        if (lastUpdated != null) {
            partnership.setLastUpdated(lastUpdated.toLocalDateTime());
        }

        return partnership;
    }
}