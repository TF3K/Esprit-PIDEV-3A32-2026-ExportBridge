package Controllers;

import Entities.Manager;
import Services.AuthenticationService;
import Utils.AppState;
import Utils.SessionManager;

import java.sql.SQLException;

public class AuthenticationController {
    private final AuthenticationService authService;

    public AuthenticationController() {
        this.authService = new AuthenticationService();
    }

    public boolean register(String firstName, String lastName, String email, String password, String confirmPassword, Long companyId){
       try {
           if (!password.equals(confirmPassword)) {
               System.out.println("Passwords do not match");
               return false;
           }

           Manager manager = authService.register(firstName,lastName,email,password,companyId);

           if (manager != null) {
               System.out.println("Registration successful for: " + manager.getEmail());
               return true;
           }

           return false;
       } catch (IllegalArgumentException e) {
           System.err.println("Validation error: " +e.getMessage());
           return false;
       } catch (SQLException e) {
           System.err.println("Database error: " + e.getMessage());
           return false;
       }
    }

    public String login(String email, String password) {
        try {
            Manager manager = authService.authenticate(email, password);

            if (manager == null) {
                System.err.println("Invalid credentials");
                return null;
            }

            String sessionId = SessionManager.createSession(manager.getId(), "LocalClient");

            AppState.setCurrentSessionId(sessionId);
            AppState.setCurrentManager(manager);

            System.out.println("Login successful for: " + manager.getEmail());
            System.out.println("Session created: " + sessionId);

            return sessionId;

        } catch (SQLException e) {
            System.err.println("Database error during login: " + e.getMessage());
            return null;
        }
    }

    public void logout() {
        String sessionId = AppState.getCurrentSessionId();

        if (sessionId != null) {
            SessionManager.invalidateSession(sessionId);
        }

        AppState.clearSession();
        System.out.println("Logged out successfully");
    }

    public boolean changePassword(String oldPassword, String newPassword, String confirmPassword) {
        try {
            if (!newPassword.equals(confirmPassword)) {
                System.err.println("New passwords do not match");
                return false;
            }

            Manager currentManager = getCurrentManager();

            if (currentManager == null) {
                System.err.println("No user logged in or session expired");
                return false;
            }

            boolean success = authService.changePassword(
                    currentManager.getId(),
                    oldPassword,
                    newPassword
            );

            if (success) {
                System.out.println("Password changed successfully");
            } else {
                System.err.println("Old password incorrect");
            }

            return success;

        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return false;
        }
    }

    public boolean validateSession() {
        return AppState.isLoggedIn();
    }

    public Manager getCurrentManager() {
        String sessionId = AppState.getCurrentSessionId();

        if (sessionId == null) {
            return null;
        }

        if (!SessionManager.isValidSession(sessionId)) {
            System.out.println("Session expired or invalid.");
            AppState.clearSession(); // Auto-logout if expired
            return null;
        }

        SessionManager.updateLastAccess(sessionId);

        return AppState.getCurrentManager();
    }
}
