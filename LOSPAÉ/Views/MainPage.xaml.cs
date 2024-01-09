using System.Text.Json;
using LOSPAÉ.ViewModels;
using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;
using Windows.UI.Popups;

namespace LOSPAÉ.Views;

public sealed partial class MainPage : Page
{
    public MainViewModel ViewModel
    {
        get;
    }

    public MainPage()
    {
        ViewModel = App.GetService<MainViewModel>();
        InitializeComponent();
        StudentsListUpdate();
    }

    public void StudentsListUpdate()
    {
        if (App.etudiants != null)
        {
            foreach (Etudiant etudiant in App.etudiants)
            {
                StudentSelector.Items.Add(etudiant.EtudiantName);
            }
        }

        StudentSelector.SelectedIndex = 0;
        CurrentNote.Text = App.etudiants[StudentSelector.SelectedIndex].EtudiantNote.ToString() + "/20";
    }

    public void SaveStudentsConfigFiles()
    {
        CurrentNote.Text = App.etudiants[StudentSelector.SelectedIndex].EtudiantNote.ToString() + "/20";
        File.WriteAllText(Path.Combine(Windows.Storage.ApplicationData.Current.LocalFolder.Path, "students.json"), JsonSerializer.Serialize(App.etudiants));
        File.WriteAllText(Path.Combine(Windows.Storage.ApplicationData.Current.LocalFolder.Path, "note_edit_events.json"), JsonSerializer.Serialize(App.noteEditEvents));
    }

    public void StudentSelector_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        Etudiant selectedStudent = App.etudiants[StudentSelector.SelectedIndex];
        CurrentNote.Text = selectedStudent.EtudiantNote.ToString() + "/20";
    }

    public void PerformStudentNoteChange(double pointsToAdd, double pointsToRemove, string operationId, string operationDesc)
    {
        App.etudiants[StudentSelector.SelectedIndex].EtudiantNote = App.etudiants[StudentSelector.SelectedIndex].EtudiantNote + pointsToAdd - pointsToRemove;
        App.noteEditEvents.Add(new NoteEditEvent(App.etudiants[StudentSelector.SelectedIndex].EtudiantName, operationId, operationDesc));
        SaveStudentsConfigFiles();
    }

    public void CP1_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 0.25, "CP1", "Absence à un cours");
    }

    public void CP2_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 0.10, "CP2", "Retard à un cours");
    }

    public void CP3_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.00, "CP3", "Envoi d'un objet dans la tête d'un collègue");
    }

    public void CP4_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "CP4", "Insolence envers le professeur");
    }

    public void CP5_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 20.00, "CP5", "Préparation et consommation d'alcool en cours");
    }

    public void CP6_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 20.00, "CP6", "Préparation et consommation de drogue en cours");
    }

    public void CP7_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.00, "CP7", "Utilisation du téléphone portable sans autorisation");
    }

    public void CP8_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 3.00, "CP8", "Ne pas se tenir correctement en cours");
    }

    public void CP9_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 1.50, "CP9", "Écouter de la musique trop fort");
    }

    public void CP10_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.00, "CP10", "Écouter de la musique sur un haut-parleur sans autorisation (PC, téléphone, enceinte…)");
    }

    public void CP11_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 10.00, "CP11", "Partir pendant une récréation sans y être autorisé");
    }

    public void CP12_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 20.00, "CP12", "Sécher un cours");
    }

    public void CP13_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "CP11", "Frapper un collègue");
    }

    public void DM1_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 3.00, "DM1", "Recourir à ChatGPT");
    }

    public void DM2_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "DM2", "Copie similaire à un collègue");
    }

    public void DM3_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "DM3", "Copier/Coller de contenu en ligne");
    }

    public void TP1_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 1.00, "TP1", "Bavardage pendant activité à faire seul");
    }

    public void TP2_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.00, "TP2", "Bavarder trop fort avec son binôme/groupe");
    }

    public void TP3_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "TP3", "Jouer à des jeux sans avoir terminé le travail demandé");
    }

    public void TP4_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.50, "TP4", "Ne pas rétablir les armoires de brassage en fin de séance");
    }

    public void TP5_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(1.00, 0.00, "TP5", "Rétablir correctement les armoires de brassage en fin de séance");
    }

    public void TP6_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 3.00, "TP6", "Recourir à ChatGPT");
    }

    public void TP7_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.00, "TP7", "Refus de travailler");
    }

    public void TP8_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "TP8", "Travail non rendu");
    }

    public void TP9_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.00, "TP9", "Nouveau jour de retard sur le rendu d'un travail");
    }

    public void TP10_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "TP10", "Dormir en cours");
    }

    public void TP11_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 3.00, "TP11", "Passivité durant un travail de groupe");
    }

    public void CO1_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 2.00, "CO1", "Bavardage en cours");
    }

    public void CO2_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.50, 0.00, "CO2", "Participation utile au cours quand interrogé");
    }

    public void CO3_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(1.00, 0.00, "CO3", "Participation volontaire utile au cours");
    }

    public void CO4_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 1.00, "CO4", "Participation volontaire inutile au cours");
    }

    public void CO5_Click(object sender, RoutedEventArgs e)
    {
        PerformStudentNoteChange(0.00, 5.00, "CO5", "Dormir en cours");
    }
}
