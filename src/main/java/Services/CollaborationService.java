package Services;

import DAO.CollaborationDAO;
import Entities.Collaboration;
import Entities.CollaborationStatus;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.util.List;

public class CollaborationService {
    private final CollaborationDAO collaborationDAO;

    public CollaborationService() {
        this.collaborationDAO = new CollaborationDAO();
    }

    public Collaboration createCollaboration(Long partnershipId, String title,
                                             String description, LocalDateTime startDate,
                                             LocalDateTime endDate) throws SQLException {

        if (partnershipId == null) {
            throw new IllegalArgumentException("Partnership ID is required");
        }

        if (title == null || title.trim().isEmpty()) {
            throw new IllegalArgumentException("Title is required");
        }

        if (startDate != null && endDate != null && endDate.isBefore(startDate)) {
            throw new IllegalArgumentException("End date cannot be before start date");
        }

        Collaboration collaboration = new Collaboration();
        collaboration.setPartnershipId(partnershipId);
        collaboration.setTitle(title);
        collaboration.setDescription(description);
        collaboration.setStartDate(startDate != null ? startDate : LocalDateTime.now());
        collaboration.setEndDate(endDate);
        collaboration.setStatus(CollaborationStatus.PLANNED);

        return collaborationDAO.create(collaboration);
    }

    public Collaboration getCollaborationById(Long id) throws SQLException {
        return collaborationDAO.findById(id);
    }

    public List<Collaboration> getPartnershipCollaborations(Long partnershipId) throws SQLException {
        return collaborationDAO.findByPartnershipId(partnershipId);
    }

    public List<Collaboration> getOngoingCollaborations(Long partnershipId) throws SQLException {
        return collaborationDAO.findOngoingByPartnershipId(partnershipId);
    }

    public boolean startCollaboration(Long collaborationId) throws SQLException {
        Collaboration collaboration = collaborationDAO.findById(collaborationId);
        if (collaboration == null) {
            throw new IllegalArgumentException("Collaboration not found");
        }

        collaboration.setStatus(CollaborationStatus.ONGOING);
        if (collaboration.getStartDate() == null) {
            collaboration.setStartDate(LocalDateTime.now());
        }

        return collaborationDAO.update(collaboration);
    }

    public boolean completeCollaboration(Long collaborationId) throws SQLException {
        Collaboration collaboration = collaborationDAO.findById(collaborationId);
        if (collaboration == null) {
            throw new IllegalArgumentException("Collaboration not found");
        }

        collaboration.setStatus(CollaborationStatus.COMPLETED);
        if (collaboration.getEndDate() == null) {
            collaboration.setEndDate(LocalDateTime.now());
        }

        return collaborationDAO.update(collaboration);
    }

    public boolean updateCollaboration(Collaboration collaboration) throws SQLException {
        if (collaboration.getId() == null) {
            throw new IllegalArgumentException("Collaboration ID is required");
        }

        return collaborationDAO.update(collaboration);
    }

    public boolean deleteCollaboration(Long collaborationId) throws SQLException {
        return collaborationDAO.delete(collaborationId);
    }

    public long countPartnershipCollaborations(Long partnershipId) throws SQLException {
        return collaborationDAO.countByPartnershipId(partnershipId);
    }
}