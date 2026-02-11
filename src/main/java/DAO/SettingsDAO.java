package DAO;

import Entities.Settings;
import Utils.DatabasePlugin;

import java.sql.*;

public class SettingsDAO {
    public Settings create(Settings settings) throws SQLException {
        String sql = "INSERT INTO settings (manager_id, language, theme, " +
                "email_notifications, push_notifications, certificate_expiry_alerts, " +
                "alert_days_before, date_format, currency, timezone) " +
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setLong(1, settings.getManagerId());
            stmt.setString(2, settings.getLanguage());
            stmt.setString(3, settings.getTheme());
            stmt.setBoolean(4, settings.isEmailNotifications());
            stmt.setBoolean(5, settings.isPushNotifications());
            stmt.setBoolean(6, settings.isCertificateExpiryAlerts());
            stmt.setInt(7, settings.getAlertDaysBefore());
            stmt.setString(8, settings.getDateFormat());
            stmt.setString(9, settings.getCurrency());
            stmt.setString(10, settings.getTimezone());

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    settings.setId(rs.getLong(1));
                }
            }
        }

        return settings;
    }

    /**
     * Find settings by manager ID
     */
    public Settings findByManagerId(Long managerId) throws SQLException {
        String sql = "SELECT * FROM settings WHERE manager_id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, managerId);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToSettings(rs);
                }
            }
        }

        return null;
    }

    public boolean update(Settings settings) throws SQLException {
        String sql = "UPDATE settings SET language = ?, theme = ?, " +
                "email_notifications = ?, push_notifications = ?, " +
                "certificate_expiry_alerts = ?, alert_days_before = ?, " +
                "date_format = ?, currency = ?, timezone = ? " +
                "WHERE manager_id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, settings.getLanguage());
            stmt.setString(2, settings.getTheme());
            stmt.setBoolean(3, settings.isEmailNotifications());
            stmt.setBoolean(4, settings.isPushNotifications());
            stmt.setBoolean(5, settings.isCertificateExpiryAlerts());
            stmt.setInt(6, settings.getAlertDaysBefore());
            stmt.setString(7, settings.getDateFormat());
            stmt.setString(8, settings.getCurrency());
            stmt.setString(9, settings.getTimezone());
            stmt.setLong(10, settings.getManagerId());

            return stmt.executeUpdate() > 0;
        }
    }

    public Settings createDefaultSettings(Long managerId) throws SQLException {
        Settings settings = new Settings();
        settings.setManagerId(managerId);
        settings.setLanguage("fr");
        settings.setTheme("light");
        settings.setEmailNotifications(true);
        settings.setPushNotifications(true);
        settings.setCertificateExpiryAlerts(true);
        settings.setAlertDaysBefore(30);
        settings.setDateFormat("DD/MM/YYYY");
        settings.setCurrency("TND");
        settings.setTimezone("Africa/Tunis");

        return create(settings);
    }

    private Settings mapResultSetToSettings(ResultSet rs) throws SQLException {
        Settings settings = new Settings();
        settings.setId(rs.getLong("id"));
        settings.setManagerId(rs.getLong("manager_id"));
        settings.setLanguage(rs.getString("language"));
        settings.setTheme(rs.getString("theme"));
        settings.setEmailNotifications(rs.getBoolean("email_notifications"));
        settings.setPushNotifications(rs.getBoolean("push_notifications"));
        settings.setCertificateExpiryAlerts(rs.getBoolean("certificate_expiry_alerts"));
        settings.setAlertDaysBefore(rs.getInt("alert_days_before"));
        settings.setDateFormat(rs.getString("date_format"));
        settings.setCurrency(rs.getString("currency"));
        settings.setTimezone(rs.getString("timezone"));
        return settings;
    }
}