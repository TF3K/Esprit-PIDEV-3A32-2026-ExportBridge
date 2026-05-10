package Utils;

import java.io.*;
import java.nio.file.*;
import java.sql.*;
import java.util.*;
import java.util.regex.*;

public class SchemaDiff {
    public static void main(String[] args) {
        String url = System.getenv("DB_URL");
        String user = System.getenv("DB_USER");
        String pass = System.getenv("DB_PASSWORD");

        if (url == null || url.isBlank())
            url = "jdbc:mysql://localhost:3306/export_bridge";
        if (user == null)
            user = "root";
        if (pass == null)
            pass = "";

        Map<String, Set<String>> entityFields = loadEntities(Paths.get("src/main/java/Entities"));

        try (Connection conn = DriverManager.getConnection(url, user, pass)) {
            DatabaseMetaData md = conn.getMetaData();
            try (ResultSet tables = md.getTables(null, null, "%", new String[] { "TABLE" })) {
                while (tables.next()) {
                    String table = tables.getString("TABLE_NAME");
                    Set<String> columns = new LinkedHashSet<>();
                    try (ResultSet cols = md.getColumns(null, null, table, "%")) {
                        while (cols.next()) {
                            columns.add(cols.getString("COLUMN_NAME"));
                        }
                    }

                    String matchedEntity = findMatchingEntity(table, entityFields.keySet());

                    System.out.println("\n=== Table: " + table + " ===");
                    System.out.println("Columns: " + columns);

                    if (matchedEntity == null) {
                        System.out.println("No matching Entity found for this table.");
                        continue;
                    }

                    Set<String> fields = entityFields.get(matchedEntity);
                    System.out.println("Entity: " + matchedEntity + " fields: " + fields);

                    Set<String> normCols = new HashSet<>();
                    for (String c : columns)
                        normCols.add(normalize(c));

                    Set<String> normFields = new HashSet<>();
                    for (String f : fields)
                        normFields.add(normalize(f));

                    Set<String> colsNotInEntity = new TreeSet<>(normCols);
                    colsNotInEntity.removeAll(normFields);

                    Set<String> fieldsNotInDB = new TreeSet<>(normFields);
                    fieldsNotInDB.removeAll(normCols);

                    System.out.println("Columns not represented in Entity: " + colsNotInEntity);
                    System.out.println("Entity fields missing in DB: " + fieldsNotInDB);
                }
            }
        } catch (SQLException e) {
            System.err.println("DB error: " + e.getMessage());
            e.printStackTrace();
            System.exit(2);
        }
    }

    private static Map<String, Set<String>> loadEntities(Path entitiesDir) {
        Map<String, Set<String>> map = new TreeMap<>();
        if (!Files.exists(entitiesDir))
            return map;

        try (DirectoryStream<Path> ds = Files.newDirectoryStream(entitiesDir, "*.java")) {
            Pattern fieldPattern = Pattern.compile("private\\s+[\\w<>\\[\\]]+\\s+([a-zA-Z0-9_]+)\\s*;");
            for (Path p : ds) {
                String name = p.getFileName().toString().replaceFirst("\\.java$", "");
                Set<String> fields = new LinkedHashSet<>();
                List<String> lines = Files.readAllLines(p);
                for (String line : lines) {
                    Matcher m = fieldPattern.matcher(line.trim());
                    if (m.find()) {
                        fields.add(m.group(1));
                    }
                }
                map.put(name, fields);
            }
        } catch (IOException e) {
            System.err.println("Failed reading entities: " + e.getMessage());
        }
        return map;
    }

    private static String normalize(String s) {
        return s.replaceAll("[_\\- ]", "").toLowerCase();
    }

    private static String findMatchingEntity(String table, Set<String> entities) {
        String t = table.replaceAll("[_\\- ]", "").toLowerCase();
        for (String e : entities) {
            String en = e.replaceAll("[_\\- ]", "").toLowerCase();
            if (t.contains(en) || en.contains(t))
                return e;
            if (t.endsWith("s") && t.substring(0, t.length() - 1).contains(en))
                return e;
            if (en.endsWith("s") && en.substring(0, en.length() - 1).contains(t))
                return e;
        }
        return null;
    }
}
