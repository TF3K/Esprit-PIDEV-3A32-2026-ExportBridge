package Utils;

import Entities.Manager;

public class AppState {
    private static String currentSessionId;
    private static Manager currentManager;

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
    }

    public static boolean isLoggedIn() {
        // Check if we have a local session ID and if it is valid on the server/manager side
        if (currentSessionId != null && SessionManager.isValidSession(currentSessionId)) {
            // Update the last access time since the app is actively checking login status
            SessionManager.updateLastAccess(currentSessionId);
            return true;
        }
        return false;
    }
}
