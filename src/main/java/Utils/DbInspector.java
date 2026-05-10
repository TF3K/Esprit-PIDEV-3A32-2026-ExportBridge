package Utils;

import java.sql.*;

public class DbInspector {
    public static void main(String[] args) {
        String url = System.getenv("DB_URL");
        String user = System.getenv("DB_USER");
        String pass = System.getenv("DB_PASSWORD");

        if (url == null || url.isBlank()) {
            url = "jdbc:mysql://localhost:3306/export_bridge";
        }
        if (user == null)
            user = "root";
        if (pass == null)
            pass = "";

        System.out.println("Using URL=" + url + " user=" + user);

        try (Connection conn = DriverManager.getConnection(url, user, pass)) {
            DatabaseMetaData md = conn.getMetaData();
            try (ResultSet rs = md.getTables(null, null, "%", new String[] { "TABLE" })) {
                System.out.println("Tables in database:");
                while (rs.next()) {
                    String tbl = rs.getString("TABLE_NAME");
                    System.out.println(tbl);
                }
            }
        } catch (SQLException e) {
            System.err.println("Failed to connect/list tables: " + e.getMessage());
            e.printStackTrace();
            System.exit(2);
        }
    }
}
