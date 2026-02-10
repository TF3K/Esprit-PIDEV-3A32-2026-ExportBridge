package src.main.java.Utils;

import java.sql.*;
import io.github.cdimascio.dotenv.Dotenv;

public class DatabasePlugin {
    private Connection connection;
    Dotenv dotenv = Dotenv.load();

    private final String URL = dotenv.get("DB_URL");
    private final String USER = dotenv.get("DB_USER");
    private final String PASSWORD = dotenv.get("DB_PASSWORD");

    private static DatabasePlugin instance;
    private DatabasePlugin() {
        try {
            connection = DriverManager.getConnection(URL, USER, PASSWORD);
            System.out.println("Connected");
        } catch (SQLException s) {
            throw new RuntimeException("Error connecting to database: " + s.getMessage(), s);
        }
    }

    public static DatabasePlugin getInstance() {
        if (instance == null) {
            instance = new DatabasePlugin();
        }
        return instance;
    }

    public Connection getConn() {
        return connection;
    }
}
