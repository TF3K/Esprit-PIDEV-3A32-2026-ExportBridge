package Controllers;

import Entities.Collaboration;
import Services.CollaborationService;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.util.List;

public class CollaborationController {
    private final CollaborationService collaborationService;

    public CollaborationController() {
        this.collaborationService = new CollaborationService();
    }

    public Collaboration createCollaboration(Long partnershipId, String title,
                                             String description, LocalDateTime startDate,
                                             LocalDateTime endDate) {
        try {
            return collaborationService.createCollaboration(partnershipId, title,
                    description, startDate, endDate);
        } catch (SQLException e) {
            System.err.println("Database error: " + e.getMessage());
            return null;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return null;
        }
    }

    public Collaboration getCollaboration(Long id) {
        try {
            return collaborationService.getCollaborationById(id);
        } catch (SQLException e) {
            System.err.println("Error fetching collaboration: " + e.getMessage());
            return null;
        }
    }

    public List<Collaboration> getPartnershipCollaborations(Long partnershipId) {
        try {
            return collaborationService.getPartnershipCollaborations(partnershipId);
        } catch (SQLException e) {
            System.err.println("Error fetching collaborations: " + e.getMessage());
            return List.of();
        }
    }

    public boolean startCollaboration(Long collaborationId) {
        try {
            return collaborationService.startCollaboration(collaborationId);
        } catch (SQLException e) {
            System.err.println("Error starting collaboration: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean completeCollaboration(Long collaborationId) {
        try {
            return collaborationService.completeCollaboration(collaborationId);
        } catch (SQLException e) {
            System.err.println("Error completing collaboration: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean updateCollaboration(Collaboration collaboration) {
        try {
            return collaborationService.updateCollaboration(collaboration);
        } catch (SQLException e) {
            System.err.println("Error updating collaboration: " + e.getMessage());
            return false;
        } catch (IllegalArgumentException e) {
            System.err.println("Validation error: " + e.getMessage());
            return false;
        }
    }

    public boolean deleteCollaboration(Long collaborationId) {
        try {
            return collaborationService.deleteCollaboration(collaborationId);
        } catch (SQLException e) {
            System.err.println("Error deleting collaboration: " + e.getMessage());
            return false;
        }
    }
}