package Utils;

import Entities.Session;

import java.io.*;
import java.nio.file.*;
import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.Map;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

public class SessionManager {
    private static final Map<String, Session> activeSessions = new ConcurrentHashMap<>();
    private static final String SESSIONS_DIR = System.getProperty("user.home") + "/.exportbridge/sessions/";
    private static final int SESSION_TIMEOUT_DAYS = 30;

    static {
        loadAllSessions();
    }

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

        saveSessionToDisk(session);

        System.out.println("Session created: " + sessionId);

        return sessionId;
    }

    public static Session getSession(String sessionId) {
        if (sessionId == null) {
            return null;
        }

        Session session = activeSessions.get(sessionId);

        if (session == null) {
            System.out.println("Session not in memory, loading from disk: " + sessionId);
            session = loadSessionFromDisk(sessionId);
            if (session != null) {
                activeSessions.put(sessionId, session);
                System.out.println("Session loaded from disk");
            } else {
                System.out.println("Session not found on disk");
            }
        }

        return session;
    }

    public static void updateLastAccess(String sessionId) {
        Session activeSession = activeSessions.get(sessionId);
        if (activeSession != null) {
            activeSession.setLastAccessTime(LocalDateTime.now());
            saveSessionToDisk(activeSession);
        }
    }

    public static void invalidateSession(String sessionId) {
        Session activeSession = activeSessions.get(sessionId);
        if (activeSession != null) {
            activeSession.setValid(false);
            activeSessions.remove(sessionId);
            deleteSessionFromDisk(sessionId);
            System.out.println("✓ Session invalidated: " + sessionId);
        }
    }

    public static boolean isValidSession(String sessionId) {
        if (sessionId == null || sessionId.isEmpty()) {
            System.out.println("Session ID is null or empty");
            return false;
        }

        Session activeSession = getSession(sessionId);

        if (activeSession == null) {
            System.out.println("Session not found: " + sessionId);
            return false;
        }

        if (!activeSession.isValid()) {
            System.out.println("Session marked as invalid");
            return false;
        }

        LocalDateTime now = LocalDateTime.now();
        LocalDateTime lastAccessTime = activeSession.getLastAccessTime();

        long daysSinceLastAccess = ChronoUnit.DAYS.between(lastAccessTime, now);

        if (daysSinceLastAccess > SESSION_TIMEOUT_DAYS) {
            System.out.println("Session expired (" + daysSinceLastAccess + " days old)");
            invalidateSession(sessionId);
            return false;
        }

        System.out.println("Session is valid (last accessed " + daysSinceLastAccess + " days ago)");
        return true;
    }

    public static Long getManagerIdFromSession(String sessionId) {
        Session activeSession = getSession(sessionId);
        if (activeSession != null && activeSession.isValid()) {
            return activeSession.getManagerId();
        }
        return null;
    }

    public static void cleanupExpiredSessions() {
        LocalDateTime now = LocalDateTime.now();
        activeSessions.entrySet().removeIf(entry -> {
            Session activeSession = entry.getValue();
            long daysSinceLastAccess = ChronoUnit.DAYS.between(activeSession.getLastAccessTime(), now);
            boolean expired = daysSinceLastAccess > SESSION_TIMEOUT_DAYS;
            if (expired) {
                deleteSessionFromDisk(entry.getKey());
            }
            return expired;
        });
    }

    private static void saveSessionToDisk(Session session) {
        try {
            File sessionsDir = new File(SESSIONS_DIR);
            if (!sessionsDir.exists()) {
                sessionsDir.mkdirs();
            }

            String filename = SESSIONS_DIR + session.getSessionId() + ".ser";
            try (ObjectOutputStream oos = new ObjectOutputStream(new FileOutputStream(filename))) {
                oos.writeObject(session);
            }

            System.out.println("Session saved to disk: " + filename);

        } catch (IOException e) {
            System.err.println("Failed to save session to disk: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private static Session loadSessionFromDisk(String sessionId) {
        try {
            String filename = SESSIONS_DIR + sessionId + ".ser";
            File sessionFile = new File(filename);

            if (!sessionFile.exists()) {
                System.out.println("Session file does not exist: " + filename);
                return null;
            }

            try (ObjectInputStream ois = new ObjectInputStream(new FileInputStream(filename))) {
                Session session = (Session) ois.readObject();
                System.out.println("Session loaded from: " + filename);
                return session;
            }

        } catch (IOException | ClassNotFoundException e) {
            System.err.println("Failed to load session from disk: " + e.getMessage());
            e.printStackTrace();
            return null;
        }
    }

    private static void deleteSessionFromDisk(String sessionId) {
        try {
            String filename = SESSIONS_DIR + sessionId + ".ser";
            boolean deleted = Files.deleteIfExists(Paths.get(filename));
            if (deleted) {
                System.out.println("✓ Session file deleted: " + filename);
            }
        } catch (IOException e) {
            System.err.println("Failed to delete session file: " + e.getMessage());
        }
    }

    private static void loadAllSessions() {
        try {
            File sessionsDir = new File(SESSIONS_DIR);
            if (!sessionsDir.exists()) {
                System.out.println("Sessions directory does not exist: " + SESSIONS_DIR);
                return;
            }

            File[] sessionFiles = sessionsDir.listFiles((dir, name) -> name.endsWith(".ser"));
            if (sessionFiles == null || sessionFiles.length == 0) {
                System.out.println("No session files found in: " + SESSIONS_DIR);
                return;
            }

            System.out.println("Found " + sessionFiles.length + " session file(s)");

            int loaded = 0;
            LocalDateTime now = LocalDateTime.now();

            for (File file : sessionFiles) {
                String sessionId = file.getName().replace(".ser", "");
                Session session = loadSessionFromDisk(sessionId);

                if (session != null && session.isValid()) {
                    long daysSinceLastAccess = ChronoUnit.DAYS.between(session.getLastAccessTime(), now);

                    if (daysSinceLastAccess <= SESSION_TIMEOUT_DAYS) {
                        activeSessions.put(sessionId, session);
                        loaded++;
                    } else {
                        file.delete();
                    }
                } else {
                    System.out.println("Invalid session, deleting: " + file.getName());
                    file.delete();
                }
            }

            if (loaded > 0) {
                System.out.println("Loaded " + loaded + " active session(s) from disk");
            } else {
                System.out.println("No valid sessions were loaded");
            }

        } catch (Exception e) {
            System.err.println("Failed to load sessions from disk: " + e.getMessage());
            e.printStackTrace();
        }
    }
}