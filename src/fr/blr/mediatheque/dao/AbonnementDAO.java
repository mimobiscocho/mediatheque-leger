package fr.blr.mediatheque.dao;

import fr.blr.mediatheque.config.Database;
import fr.blr.mediatheque.model.Abonnement;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;

/** Accès aux données des types d'abonnement (lecture seule). */
public class AbonnementDAO {

    public List<Abonnement> findAll() throws SQLException {
        List<Abonnement> list = new ArrayList<>();
        String sql = "SELECT * FROM abonnement ORDER BY tarif";
        try (Statement st = Database.getConnection().createStatement();
             ResultSet rs = st.executeQuery(sql)) {
            while (rs.next()) {
                Abonnement a = new Abonnement();
                a.setId(rs.getInt("id"));
                a.setLibelle(rs.getString("libelle"));
                a.setTarif(rs.getDouble("tarif"));
                a.setDureeMois(rs.getInt("duree_mois"));
                a.setQuotaEmprunts(rs.getInt("quota_emprunts"));
                list.add(a);
            }
        }
        return list;
    }
}
