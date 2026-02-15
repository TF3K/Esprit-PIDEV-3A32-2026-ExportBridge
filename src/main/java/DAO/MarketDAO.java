package DAO;

import Entities.Market;
import Utils.DatabasePlugin;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class MarketDAO implements GenericDAO<Market, Long> {

    @Override
    public Market create(Market market) throws SQLException {
        String sql = "INSERT INTO markets (country_code, name, region, is_eu, description, trade_agreement) " +
                "VALUES (?, ?, ?, ?, ?, ?)";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {

            stmt.setString(1, market.getCountryCode());
            stmt.setString(2, market.getName());
            stmt.setString(3, market.getRegion());
            stmt.setBoolean(4, market.isEu());
            stmt.setString(5, market.getDescription());
            stmt.setString(6, market.getTradeAgreement());

            stmt.executeUpdate();

            try (ResultSet rs = stmt.getGeneratedKeys()) {
                if (rs.next()) {
                    market.setId(rs.getLong(1));
                }
            }
        }

        return market;
    }

    @Override
    public Market findById(Long id) throws SQLException {
        String sql = "SELECT * FROM markets WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToEntity(rs);
                }
            }
        }

        return null;
    }

    @Override
    public List<Market> findAll() throws SQLException {
        String sql = "SELECT * FROM markets ORDER BY name";
        List<Market> markets = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                markets.add(mapResultSetToEntity(rs));
            }
        }

        return markets;
    }

    @Override
    public boolean update(Market market) throws SQLException {
        String sql = "UPDATE markets SET country_code = ?, name = ?, region = ?, " +
                "is_eu = ?, description = ?, trade_agreement = ? WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, market.getCountryCode());
            stmt.setString(2, market.getName());
            stmt.setString(3, market.getRegion());
            stmt.setBoolean(4, market.isEu());
            stmt.setString(5, market.getDescription());
            stmt.setString(6, market.getTradeAgreement());
            stmt.setLong(7, market.getId());

            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean delete(Long id) throws SQLException {
        String sql = "DELETE FROM markets WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);
            return stmt.executeUpdate() > 0;
        }
    }

    @Override
    public boolean exists(Long id) throws SQLException {
        String sql = "SELECT COUNT(*) FROM markets WHERE id = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setLong(1, id);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getInt(1) > 0;
                }
            }
        }

        return false;
    }

    @Override
    public long count() throws SQLException {
        String sql = "SELECT COUNT(*) FROM markets";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            if (rs.next()) {
                return rs.getLong(1);
            }
        }

        return 0;
    }

    public Market findByCountryCode(String countryCode) throws SQLException {
        String sql = "SELECT * FROM markets WHERE country_code = ?";

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, countryCode);

            try (ResultSet rs = stmt.executeQuery()) {
                if (rs.next()) {
                    return mapResultSetToEntity(rs);
                }
            }
        }

        return null;
    }

    public List<Market> findEUMarkets() throws SQLException {
        String sql = "SELECT * FROM markets WHERE is_eu = true ORDER BY name";
        List<Market> markets = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                markets.add(mapResultSetToEntity(rs));
            }
        }

        return markets;
    }

    public List<Market> findByRegion(String region) throws SQLException {
        String sql = "SELECT * FROM markets WHERE region = ? ORDER BY name";
        List<Market> markets = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            stmt.setString(1, region);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    markets.add(mapResultSetToEntity(rs));
                }
            }
        }

        return markets;
    }

    public List<Market> search(String query) throws SQLException {
        String sql = "SELECT * FROM markets WHERE name LIKE ? OR country_code LIKE ? ORDER BY name";
        List<Market> markets = new ArrayList<>();

        try (Connection conn = DatabasePlugin.getInstance().getConn();
             PreparedStatement stmt = conn.prepareStatement(sql)) {

            String searchPattern = "%" + query + "%";
            stmt.setString(1, searchPattern);
            stmt.setString(2, searchPattern);

            try (ResultSet rs = stmt.executeQuery()) {
                while (rs.next()) {
                    markets.add(mapResultSetToEntity(rs));
                }
            }
        }

        return markets;
    }

    private Market mapResultSetToEntity(ResultSet rs) throws SQLException {
        Market market = new Market();
        market.setId(rs.getLong("id"));
        market.setCountryCode(rs.getString("country_code"));
        market.setName(rs.getString("name"));
        market.setRegion(rs.getString("region"));
        market.setEu(rs.getBoolean("is_eu"));
        market.setDescription(rs.getString("description"));
        market.setTradeAgreement(rs.getString("trade_agreement"));
        return market;
    }
}