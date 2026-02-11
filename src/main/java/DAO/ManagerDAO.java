package DAO;

import Entities.Manager;
import Utils.DatabasePlugin;

import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class ManagerDAO {

    public Manager createManager(Manager manager) throws SQLException {
        String sql = "INSERT INTO managers (first_name, last_name, email, password, company_id, created_at) " +
                "VALUES (?,?,?,?,?,?)";
        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            stmt.setString(1, manager.getFirstName());
            stmt.setString(2, manager.getLastName());
            stmt.setString(3, manager.getEmail());
            stmt.setString(4, manager.getPassword());
            if (manager.getCompanyId() != null) {
                stmt.setLong(5, manager.getCompanyId());
            } else {
                stmt.setNull(5, Types.BIGINT);
            }
            stmt.setTimestamp(6, Timestamp.valueOf(manager.getCreatedAt()));

            int affectedRows = stmt.executeUpdate();

            if (affectedRows == 0) {
                throw new SQLException("Creating manager failed, no rows affected.");
            }

            try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                if (generatedKeys.next()) {
                    manager.setId(generatedKeys.getLong(1));
                } else {
                    throw new SQLException("Creating manager failed, no ID obtained.");
                }
            }
        }
        return manager;
    }

    public Manager findManagerById(Long id) throws SQLException {
        String sql = "SELECT * FROM managers WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1,id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToManager(rs);
                }
            }
        }
        return null;
    }

    public Manager findByEmail(String email) throws SQLException {
        String sql = "SELECT * FROM managers WHERE email = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, email);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToManager(rs);
                }
            }
        }

        return null;
    }

    public boolean update(Manager manager) throws SQLException {
        String sql = "UPDATE managers SET first_name = ?, last_name = ?, " +
                "email = ?, password = ?, company_id = ?, last_login = ? " +
                "WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1,manager.getFirstName());
            stmt.setString(2,manager.getLastName());
            stmt.setString(3,manager.getEmail());
            stmt.setString(4,manager.getPassword());

            if (manager.getCompanyId() != null) {
                stmt.setLong(5, manager.getCompanyId());
            } else {
                stmt.setNull(5, Types.BIGINT);
            }

            if (manager.getLastLogin() != null) {
                stmt.setTimestamp(6, Timestamp.valueOf(manager.getLastLogin()));
            } else {
                stmt.setNull(6, Types.TIMESTAMP);
            }

            stmt.setLong(7, manager.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM managers WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    public boolean updateLastLogin(Long managerId) throws SQLException {
        String sql = "UPDATE managers SET last_login = ? WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setTimestamp(1, Timestamp.valueOf(LocalDateTime.now()));
            stmt.setLong(2, managerId);

            return stmt.executeUpdate() > 0;
        }
    }

    public boolean emailExists(String email) throws SQLException {
        String sql = "SELECT COUNT(*) FROM managers WHERE email = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, email);
            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt(1) > 0;
                }
            }
        }
        return false;
    }

    public List<Manager> findByCompanyId(Long companyId) throws SQLException {
        String sql = "SELECT * FROM managers WHERE company_id = ?";
        List<Manager> managers = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setLong(1, companyId);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    managers.add(mapResultSetToManager(rs));
                }
            }
        }
        return managers;
    }

    private Manager mapResultSetToManager(ResultSet rs) throws SQLException {
        Manager manager = Manager.builder()
                .id(rs.getLong("id"))
                .firstName(rs.getString("first_name"))
                .lastName(rs.getString("last_name"))
                .email(rs.getString("email"))
                .password(rs.getString("password"))
                .build();

        long companyId = rs.getLong("company_id");
        if (!rs.wasNull()) {
            manager.setCompanyId(companyId);
        }

        Timestamp createdAt = rs.getTimestamp("created_at");
        if (createdAt != null) {
            manager.setCreatedAt(createdAt.toLocalDateTime());
        }

        Timestamp lastLogin = rs.getTimestamp("last_login");
        if (lastLogin != null) {
            manager.setLastLogin(lastLogin.toLocalDateTime());
        }

        return manager;
    }
}
