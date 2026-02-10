package src.main.java.Entities;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;
import java.util.HashMap;
import java.util.Map;


@Data
@AllArgsConstructor
@Builder
public class Session {
    private String sessionId;
    private Long managerId;
    private LocalDateTime creationTime;
    private LocalDateTime lastAccessTime;
    private volatile boolean isValid;
    private String remoteAddress;

    @Builder.Default
    private Map<String, Object> attributes = new HashMap<>();

    public Object getAttribute(String key){
        return this.attributes.get(key);
    }

    public void setAttribute(String key, Object value){
        this.attributes.put(key, value);
    }

}
