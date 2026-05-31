package fr.blr.mediatheque.dao;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.model.Livre;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Types;
import java.util.ArrayList;
import java.util.List;

/** Accès aux données des livres (CRUD via requêtes préparées). */
public class LivreDAO {

    public List<Livre> findAll() throws SQLException {
        return query("SELECT * FROM livre ORDER BY titre");
    }

    /** Livres ayant au moins un exemplaire disponible (pour les prêts). */
    public List<Livre> findDisponibles() throws SQLException {
        return query("SELECT * FROM livre WHERE quantite_disponible > 0 ORDER BY titre");
    }

    private List<Livre> query(String sql) throws SQLException {
        List<Livre> list = new ArrayList<>();
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public void insert(Livre l) throws SQLException {
        String sql = "INSERT INTO livre "
            + "(titre, auteur, isbn, editeur, annee_publication, genre, quantite_totale, quantite_disponible) "
            + "VALUES (?,?,?,?,?,?,?,?)";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            bind(ps, l);
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) l.setId(keys.getInt(1));
            }
        }
    }

    public void update(Livre l) throws SQLException {
        String sql = "UPDATE livre SET titre=?, auteur=?, isbn=?, editeur=?, annee_publication=?, "
            + "genre=?, quantite_totale=?, quantite_disponible=? WHERE id=?";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql)) {
            bind(ps, l);
            ps.setInt(9, l.getId());
            ps.executeUpdate();
        }
    }

    public void delete(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement("DELETE FROM livre WHERE id=?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    public int count() throws SQLException {
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM livre")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    private void bind(PreparedStatement ps, Livre l) throws SQLException {
        ps.setString(1, l.getTitre());
        ps.setString(2, l.getAuteur());
        ps.setString(3, l.getIsbn());
        ps.setString(4, l.getEditeur());
        if (l.getAnneePublication() == null) ps.setNull(5, Types.INTEGER);
        else ps.setInt(5, l.getAnneePublication());
        ps.setString(6, l.getGenre());
        ps.setInt(7, l.getQuantiteTotale());
        ps.setInt(8, l.getQuantiteDisponible());
    }

    private Livre map(ResultSet rs) throws SQLException {
        Livre l = new Livre();
        l.setId(rs.getInt("id"));
        l.setTitre(rs.getString("titre"));
        l.setAuteur(rs.getString("auteur"));
        l.setIsbn(rs.getString("isbn"));
        l.setEditeur(rs.getString("editeur"));
        int annee = rs.getInt("annee_publication");
        l.setAnneePublication(rs.wasNull() ? null : annee);
        l.setGenre(rs.getString("genre"));
        l.setQuantiteTotale(rs.getInt("quantite_totale"));
        l.setQuantiteDisponible(rs.getInt("quantite_disponible"));
        return l;
    }
}
