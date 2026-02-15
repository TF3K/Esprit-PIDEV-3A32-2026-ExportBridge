package DAO;

import Entities.Collaboration;
import Entities.CollaborationStatus;
import Utils.DatabasePlugin;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class CollaborationDAO implements GenericDAO<Collaboration, Long> {

    @Override
    public Collaboration create(Collaboration collaboration) throws SQLException {
        String sql = "INSERT INTO collaborations (partnership_id, title, description, " +
                "start_date, end_date, status) " +
                "VALUES (?, ?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setLong(1, collaboration.getPartnershipId());
            stmt.setString(2, collaboration.getTitle());
            stmt.setString(3, collaboration.getDescription());

            if (collaboration.getStartDate() != null) {
                stmt.setDate(4, Date.valueOf(collaboration.getStartDate().toLocalDate()));
            } else {
                stmt.setNull(4, Types.DATE);
            }

            if (collaboration.getEndDate() != null) {
                stmt.setDate(5, Date.valueOf(collaboration.getEndDate().toLocalDate()));
            } else {
                stmt.setNull(5, Types.DATE);
            }

            stmt.setString(6, collaboration.getStatus().name());

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    collaboration.setId(rs.getLong(1));
                }
            }

            Collaboration created = findById(collaboration.getId());
            if (created != null) {
                collaboration.setCreatedAt(created.getCreatedAt());
                collaboration.setLastUpdated(created.getLastUpdated());
            }
        }

        return collaboration;
    }

    @Override
    public Collaboration findById(Long id) throws SQLException {
        String sql = "SELECT * FROM collaborations WHERE id = ?";

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
    public List<Collaboration> findAll() throws SQLException {
        String sql = "SELECT * FROM collaborations ORDER BY start_date DESC";
        List<Collaboration> collaborations = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                collaborations.add(mapResultSetToEntity(rs));
            }
        }

        return collaborations;
    }

    @Override
    public boolean update(Collaboration collaboration) throws SQLException {
        String sql = "UPDATE collaborations SET partnership_id = ?, title = ?, description = ?, " +
                "start_date = ?, end_date = ?, status = ? WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, collaboration.getPartnershipId());
            stmt.setString(2, collaboration.getTitle());
            stmt.setString(3, collaboration.getDescription());

            if (collaboration.getStartDate() != null) {
                stmt.setDate(4, Date.valueOf(collaboration.getStartDate().toLocalDate()));
            } else {
                stmt.setNull(4, Types.DATE);
            }

            if (collaboration.getEndDate() != null) {
                stmt.setDate(5, Date.valueOf(collaboration.getEndDate().toLocalDate()));
            } else {
                stmt.setNull(5, Types.DATE);
            }

            stmt.setString(6, collaboration.getStatus().name());
            stmt.setLong(7, collaboration.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM collaborations WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM collaborations WHERE id = ?";

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
        String sql = "SELECT COUNT(*) FROM collaborations";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }

        return 0;
    }

    public List<Collaboration> findByPartnershipId(Long partnershipId) throws SQLException {
        String sql = "SELECT * FROM collaborations WHERE partnership_id = ? ORDER BY start_date DESC";
        List<Collaboration> collaborations = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, partnershipId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    collaborations.add(mapResultSetToEntity(rs));
                }
            }
        }

        return collaborations;
    }

    public List<Collaboration> findOngoingByPartnershipId(Long partnershipId) throws SQLException {
        String sql = "SELECT * FROM collaborations " +
                "WHERE partnership_id = ? " +
                "AND status = 'ONGOING' " +
                "AND (end_date IS NULL OR end_date > NOW()) " +
                "ORDER BY start_date DESC";
        List<Collaboration> collaborations = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, partnershipId);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    collaborations.add(mapResultSetToEntity(rs));
                }
            }
        }

        return collaborations;
    }

    public List<Collaboration> findByStatus(CollaborationStatus status) throws SQLException {
        String sql = "SELECT * FROM collaborations WHERE status = ? ORDER BY start_date DESC";
        List<Collaboration> collaborations = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, status.name());

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    collaborations.add(mapResultSetToEntity(rs));
                }
            }
        }

        return collaborations;
    }

    public long countByPartnershipId(Long partnershipId) throws SQLException {
        String sql = "SELECT COUNT(*) FROM collaborations WHERE partnership_id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, partnershipId);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getLong(1);
                }
            }
        }

        return 0;
    }

    private Collaboration mapResultSetToEntity(ResultSet rs) throws SQLException {
        Collaboration collaboration = new Collaboration();
        collaboration.setId(rs.getLong("id"));
        collaboration.setPartnershipId(rs.getLong("partnership_id"));
        collaboration.setTitle(rs.getString("title"));
        collaboration.setDescription(rs.getString("description"));

        Date startDate = rs.getDate("start_date");
        if (startDate != null) {
            collaboration.setStartDate(startDate.toLocalDate().atStartOfDay());
        }

        Date endDate = rs.getDate("end_date");
        if (endDate != null) {
            collaboration.setEndDate(endDate.toLocalDate().atStartOfDay());
        }

        collaboration.setStatus(CollaborationStatus.valueOf(rs.getString("status")));

        Timestamp createdAt = rs.getTimestamp("created_at");
        if (createdAt != null) {
            collaboration.setCreatedAt(createdAt.toLocalDateTime());
        }

        Timestamp lastUpdated = rs.getTimestamp("last_updated");
        if (lastUpdated != null) {
            collaboration.setLastUpdated(lastUpdated.toLocalDateTime());
        }

        return collaboration;
    }
}