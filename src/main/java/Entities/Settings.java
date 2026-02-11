package Entities;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

@Data
@AllArgsConstructor
@NoArgsConstructor
public class Settings {
    private Long id;
    private Long managerId;
    private String language;
    private String theme;
    private boolean emailNotifications;
    private boolean pushNotifications;
    private boolean certificateExpiryAlerts;
    private Integer alertDaysBefore;
    private String dateFormat;
    private String currency;
    private String timezone;
}
