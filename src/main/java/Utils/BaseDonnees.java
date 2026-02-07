package Utils;

import java.sql.*;
import io.github.cdimascio.dotenv.Dotenv;

public class BaseDonnees {
    private Connection connection;
    Dotenv dotenv = Dotenv.load();
    private final String URL = dotenv.get("DB_URL");
    private final String USER = dotenv.get("DB_USER");
    private final String PASSWORD = dotenv.get("DB_PASSWORD");

    private static BaseDonnees instance;
    private BaseDonnees () {
        try {
            connection = DriverManager.getConnection(URL, USER, PASSWORD);
            System.out.println("Connected");
        } catch (SQLException s) {
            System.out.println(s.getMessage());
        }
    }

    public static BaseDonnees getInstance() {
        if (instance == null) {
            instance = new BaseDonnees();
        }
        return instance;
    }

    public Connection getConn() {
        return connection;
    }
}
