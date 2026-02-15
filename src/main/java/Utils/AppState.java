package Utils;

import Entities.Manager;

import java.io.*;
import java.nio.file.*;

public class AppState {
    private static String currentSessionId;
    private static Manager currentManager;

    private static final String SESSION_FILE = System.getProperty("user.home") + "/.exportbridge/session.dat";

    public static void setCurrentSessionId(String sessionId) {
        currentSessionId = sessionId;
    }

    public static String getCurrentSessionId() {
        return currentSessionId;
    }

    public static void setCurrentManager(Manager manager) {
        currentManager = manager;
    }

    public static Manager getCurrentManager() {
        return currentManager;
    }

    public static void clearSession() {
        currentSessionId = null;
        currentManager = null;
        deleteSessionFile();
    }

    public static boolean isLoggedIn() {
        if (currentSessionId == null) {
            return false;
        }
        return SessionManager.isValidSession(currentSessionId);
    }

    public static void saveSessionToFile(String sessionId) {
        try {
            File sessionFile = new File(SESSION_FILE);
            File parentDir = sessionFile.getParentFile();
            if (parentDir != null && !parentDir.exists()) {
                parentDir.mkdirs();
            }

            Files.writeString(Paths.get(SESSION_FILE), sessionId);
            System.out.println("Session ID saved to file: " + SESSION_FILE);

        } catch (IOException e) {
            System.err.println("Failed to save session ID: " + e.getMessage());
            e.printStackTrace();
        }
    }

    public static String loadSessionFromFile() {
        try {
            File sessionFile = new File(SESSION_FILE);
            if (!sessionFile.exists()) {
                System.out.println("No session file found at: " + SESSION_FILE);
                return null;
            }

            String sessionId = Files.readString(Paths.get(SESSION_FILE)).trim();
            System.out.println("⚙ Session ID loaded from file: " + sessionId);

            if (SessionManager.isValidSession(sessionId)) {
                System.out.println("Valid session restored from file");
                return sessionId;
            } else {
                System.out.println("Session expired or invalid");
                deleteSessionFile();
                return null;
            }

        } catch (IOException e) {
            System.err.println("Failed to load session: " + e.getMessage());
            return null;
        }
    }

    public static void deleteSessionFile() {
        try {
            boolean deleted = Files.deleteIfExists(Paths.get(SESSION_FILE));
            if (deleted) {
                System.out.println("Session ID file deleted");
            }
        } catch (IOException e) {
            System.err.println("Failed to delete session file: " + e.getMessage());
        }
    }
}