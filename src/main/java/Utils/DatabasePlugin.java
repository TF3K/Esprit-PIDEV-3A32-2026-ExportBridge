package Utils;

import java.sql.*;
import io.github.cdimascio.dotenv.Dotenv;

public class DatabasePlugin {
    private Connection connection;

    private final Dotenv dotenv = Dotenv.configure()
            .directory("./")
            .ignoreIfMalformed()
            .ignoreIfMissing()
            .load();

    private final String URL = dotenv.get("DB_URL");
    private final String USER = dotenv.get("DB_USER");
    private final String PASSWORD = dotenv.get("DB_PASSWORD");

    private static DatabasePlugin instance;

    private DatabasePlugin() {
        connect();
    }

    public static synchronized DatabasePlugin getInstance() {
        if (instance == null) {
            instance = new DatabasePlugin();
        }
        return instance;
    }

    private void connect() {
        try {
            connection = DriverManager.getConnection(URL, USER, PASSWORD);
            System.out.println("Database Connected");
        } catch (SQLException s) {
            throw new RuntimeException("Error connecting to database: " + s.getMessage(), s);
        }
    }


    public Connection getConn() {
        try {
            if (connection == null || connection.isClosed()) {
                System.out.println("Connection was closed. Reconnecting...");
                connect();
            }
        } catch (SQLException e) {
            throw new RuntimeException("Error checking connection status", e);
        }
        return connection;
    }
}