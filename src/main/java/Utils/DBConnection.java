package Utils;

import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.util.Properties;

public class DBConnection {
    private static Connection cnx;

    public static Connection getConnection() {
        if (cnx != null) return cnx;

        try (InputStream is = DBConnection.class.getClassLoader().getResourceAsStream("db.properties")) {
            Properties props = new Properties();
            props.load(is);

            String url = props.getProperty("db.url");
            String user = props.getProperty("db.user");
            String pass = props.getProperty("db.password");

            cnx = DriverManager.getConnection(url, user, pass);
            return cnx;

        } catch (Exception e) {
            throw new RuntimeException("Erreur connexion DB: " + e.getMessage(), e);
        }
    }
}

