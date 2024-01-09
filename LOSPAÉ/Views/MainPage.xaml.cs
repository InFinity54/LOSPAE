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
    }

    public void SaveStudentsConfigFile()
    {
        File.WriteAllText(Path.Combine(Windows.Storage.ApplicationData.Current.LocalFolder.Path, "students.json"), JsonSerializer.Serialize(App.etudiants));
    }

    public void StudentSelector_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        Etudiant selectedStudent = App.etudiants[StudentSelector.SelectedIndex];
        CurrentNote.Text = selectedStudent.EtudiantNote.ToString() + "/20";
    }

    public void CP1_Click(object sender, RoutedEventArgs e)
    {
        App.etudiants[StudentSelector.SelectedIndex].EtudiantNote = App.etudiants[StudentSelector.SelectedIndex].EtudiantNote - 0.25;
        CurrentNote.Text = App.etudiants[StudentSelector.SelectedIndex].EtudiantNote.ToString() + "/20";
        SaveStudentsConfigFile();
    }
}
