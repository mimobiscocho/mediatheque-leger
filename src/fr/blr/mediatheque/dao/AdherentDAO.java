package fr.blr.mediatheque.dao;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.model.Adherent;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Types;
import java.util.ArrayList;
import java.util.List;

/** Accès aux données des adhérents (CRUD via requêtes préparées). */
public class AdherentDAO {

    private static final String SELECT_BASE =
        "SELECT a.*, ab.libelle AS abonnement_libelle "
        + "FROM adherent a LEFT JOIN abonnement ab ON a.abonnement_id = ab.id ";

    public List<Adherent> findAll() throws SQLException {
        List<Adherent> list = new ArrayList<>();
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery(SELECT_BASE + "ORDER BY a.nom, a.prenom")) {
            while (rs.next()) list.add(map(rs));
        }
        return list;
    }

    public Adherent findById(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement(SELECT_BASE + "WHERE a.id = ?")) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                return rs.next() ? map(rs) : null;
            }
        }
    }

    public void insert(Adherent a) throws SQLException {
        String sql = "INSERT INTO adherent "
            + "(nom, prenom, email, telephone, adresse, abonnement_id, date_inscription, date_fin_abonnement, actif) "
            + "VALUES (?,?,?,?,?,?,?,?,?)";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
            bind(ps, a);
            ps.executeUpdate();
            try (ResultSet keys = ps.getGeneratedKeys()) {
                if (keys.next()) a.setId(keys.getInt(1));
            }
        }
    }

    public void update(Adherent a) throws SQLException {
        String sql = "UPDATE adherent SET nom=?, prenom=?, email=?, telephone=?, adresse=?, "
            + "abonnement_id=?, date_inscription=?, date_fin_abonnement=?, actif=? WHERE id=?";
        try (PreparedStatement ps = Database.getConnection().prepareStatement(sql)) {
            bind(ps, a);
            ps.setInt(10, a.getId());
            ps.executeUpdate();
        }
    }

    public void delete(int id) throws SQLException {
        try (PreparedStatement ps = Database.getConnection().prepareStatement("DELETE FROM adherent WHERE id=?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    public int count() throws SQLException {
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery("SELECT COUNT(*) FROM adherent")) {
            return rs.next() ? rs.getInt(1) : 0;
        }
    }

    private void bind(PreparedStatement ps, Adherent a) throws SQLException {
        ps.setString(1, a.getNom());
        ps.setString(2, a.getPrenom());
        ps.setString(3, a.getEmail());
        ps.setString(4, a.getTelephone());
        ps.setString(5, a.getAdresse());
        if (a.getAbonnementId() == null) ps.setNull(6, Types.INTEGER);
        else ps.setInt(6, a.getAbonnementId());
        ps.setString(7, a.getDateInscription());
        ps.setString(8, a.getDateFinAbonnement());
        ps.setBoolean(9, a.isActif());
    }

    private Adherent map(ResultSet rs) throws SQLException {
        Adherent a = new Adherent();
        a.setId(rs.getInt("id"));
        a.setNom(rs.getString("nom"));
        a.setPrenom(rs.getString("prenom"));
        a.setEmail(rs.getString("email"));
        a.setTelephone(rs.getString("telephone"));
        a.setAdresse(rs.getString("adresse"));
        int aid = rs.getInt("abonnement_id");
        a.setAbonnementId(rs.wasNull() ? null : aid);
        a.setDateInscription(rs.getString("date_inscription"));
        a.setDateFinAbonnement(rs.getString("date_fin_abonnement"));
        a.setActif(rs.getBoolean("actif"));
        a.setAbonnementLibelle(rs.getString("abonnement_libelle"));
        return a;
    }
}
