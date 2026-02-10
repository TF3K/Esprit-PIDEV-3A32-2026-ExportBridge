import org.junit.jupiter.api.AfterAll;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import src.main.java.Utils.DatabasePlugin;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.lang.reflect.Field;
import java.sql.Connection;
import java.sql.SQLException;

import static org.junit.jupiter.api.Assertions.*;

public class DatabasePluginTest {
    @BeforeAll
    public static void createEnvFile() throws IOException {
        File envFile = new File(".env");
        try (FileWriter fw = new FileWriter(envFile)) {
            fw.write("DB_URL=jdbc:mysql://localhost:3306/export_bridge\n");
            fw.write("DB_USER=root\n");
            fw.write("DB_PASSWORD=\n");
        }
    }

    @AfterAll
    public static void deleteEnvFile() {
        File envFile = new File(".env");
        if (envFile.exists()) {
            envFile.delete();
        }
    }

    @BeforeEach
    public void resetSingleton() throws Exception {
        Field instance = DatabasePlugin.class.getDeclaredField("instance");
        instance.setAccessible(true);
        instance.set(null, null);
    }

    @Test
    public void testSingletonInstance() {
        DatabasePlugin firstCall = DatabasePlugin.getInstance();
        assertNotNull(firstCall, "Instance should not be null");

        DatabasePlugin secondCall = DatabasePlugin.getInstance();

        assertSame(firstCall, secondCall, "Both calls should return the same object");
    }

    @Test
    public void testConnectionIsCreated() throws SQLException {
        DatabasePlugin plugin = DatabasePlugin.getInstance();

        Connection conn = plugin.getConn();

        assertNotNull(conn, "Connection object should not be null");
        assertFalse(conn.isClosed(), "Connection should be open");
    }
}
