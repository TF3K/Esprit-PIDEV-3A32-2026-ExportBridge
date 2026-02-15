package Entities;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.io.Serial;
import java.io.Serializable;
import java.time.LocalDateTime;
import java.util.HashMap;
import java.util.Map;


@Data
@AllArgsConstructor
@Builder
public class Session implements Serializable {
    @Serial
    private static final long serialVersionUID = 1L;

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
