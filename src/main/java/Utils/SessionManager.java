package src.main.java.Utils;

import src.main.java.Entities.Session;

import java.time.LocalDateTime;
import java.util.Map;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

public class SessionManager {
    private static final Map<String, Session> activeSessions = new ConcurrentHashMap<>();

    public static String createSession(Long managerId, String remoteAddress) {
        String sessionId = UUID.randomUUID().toString();
        Session session = Session.builder()
                .sessionId(sessionId)
                .managerId(managerId)
                .creationTime(LocalDateTime.now())
                .lastAccessTime(LocalDateTime.now())
                .isValid(true)
                .remoteAddress(remoteAddress)
                .build();

        activeSessions.put(sessionId, session);

        return sessionId;
    }

    public static Session getSession(String sessionId) {
        return activeSessions.get(sessionId);
    }

    public static void updateLastAccess(String sessionId) {
        Session activeSession = activeSessions.get(sessionId);
        if (activeSession != null) {
            activeSession.setLastAccessTime(LocalDateTime.now());
        }
    }

    public static void invalidateSession(String sessionId) {
        Session activeSession = activeSessions.get(sessionId);
        if (activeSession != null) {
            activeSession.setValid(false);
            activeSessions.remove(sessionId);
        }
    }

    public static boolean isValidSession(String sessionId) {
        Session activeSession = activeSessions.get(sessionId);
        if (activeSession == null || !activeSession.isValid()) {
            return false;
        }
        LocalDateTime now = LocalDateTime.now();
        LocalDateTime lastAccessTime = activeSession.getLastAccessTime();
        if (lastAccessTime.plusMinutes(30).isBefore(now)) {
            invalidateSession(sessionId);
            return false;
        }
        return true;
    }

    public static Long getManagerIdFromSession(String sessionId) {
        Session activeSession = activeSessions.get(sessionId);
        if (activeSession != null && activeSession.isValid()) {
            return activeSession.getManagerId();
        }
        return null;
    }

    public static void cleanupExpiredSessions(String sessionId) {
        LocalDateTime now = LocalDateTime.now();
        activeSessions.entrySet().removeIf(entry -> {
            Session activeSession = entry.getValue();
            return activeSession.getLastAccessTime().plusMinutes(30).isBefore(now);
        });
    }
}
