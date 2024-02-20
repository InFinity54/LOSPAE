using System.Diagnostics.Eventing.Reader;
using System.Text.Json;
using LOSPAÉ.ViewModels;

using Microsoft.UI.Xaml.Controls;

namespace LOSPAÉ.Views;

public sealed partial class HistoryPage : Page
{
    public HistoryViewModel ViewModel
    {
        get;
    }

    public HistoryPage()
    {
        ViewModel = App.GetService<HistoryViewModel>();
        InitializeComponent();

    }

    private void HistoryDataGrid_SelectionChanged(object sender, SelectionChangedEventArgs e)
    {
        if (HistoryDataGrid.SelectedItems.Count > 0)
        {
            HistoryDataEventRemove.IsEnabled = true;
        }
        else
        {
            HistoryDataEventRemove.IsEnabled = false;
        }
    }

    private async void UndoEventFromHistory(object sender, Microsoft.UI.Xaml.RoutedEventArgs e)
    {
        NoteEditEvent selectedEvent = (NoteEditEvent)HistoryDataGrid.SelectedItems[0];

        ContentDialog confirmDialog = new ContentDialog()
        {
            XamlRoot = App.MainWindow.Content.XamlRoot,
            Title = "LOSPAÉ",
            Content = "Voulez-vous vraiment supprimer l'événement \"" + selectedEvent.NoteChangeDesc + "\" survenu le " + selectedEvent.ChangedOccurDateTime.ToLongDateString() + " concernant " + selectedEvent.EtudiantName + " ?" + Environment.NewLine + "Cette opération est irréversible.",
            PrimaryButtonText = "Oui",
            SecondaryButtonText = "Non",
            DefaultButton = ContentDialogButton.Primary
        };

        var result = await confirmDialog.ShowAsync();

        if (result == ContentDialogResult.Primary)
        {
            Etudiant eventEtudiant = App.etudiants.Find(e => e.EtudiantName == selectedEvent.EtudiantName);
            int eventEtudiantIndex = App.etudiants.IndexOf(eventEtudiant);

            if (eventEtudiant != null)
            {
                switch (selectedEvent.NoteChangeId)
                {
                    case "CP2":
                        eventEtudiant.EtudiantNote += 0.50;
                        break;
                    case "CP1":
                    case "TP1":
                    case "CO4":
                        eventEtudiant.EtudiantNote += 1.00;
                        break;
                    case "CP9":
                        eventEtudiant.EtudiantNote += 1.50;
                        break;
                    case "CP3":
                    case "CP7":
                    case "CP10":
                    case "CP15":
                    case "TP2":
                    case "TP7":
                    case "TP9":
                    case "TP13":
                    case "TP14":
                    case "CO1":
                    case "DS1":
                    case "DS5":
                        eventEtudiant.EtudiantNote += 2.00;
                        break;
                    case "TP4":
                        eventEtudiant.EtudiantNote += 2.50;
                        break;
                    case "CP8":
                    case "DM1":
                    case "TP6":
                    case "TP11":
                    case "DS2":
                        eventEtudiant.EtudiantNote += 3.00;
                        break;
                    case "CP4":
                    case "CP13":
                    case "DM2":
                    case "DM3":
                    case "TP3":
                    case "TP8":
                    case "TP10":
                    case "TP12":
                    case "CO5":
                        eventEtudiant.EtudiantNote += 5.00;
                        break;
                    case "CP11":
                    case "DS3":
                    case "DS4":
                        eventEtudiant.EtudiantNote += 10.00;
                        break;
                    case "CP5":
                    case "CP6":
                    case "CP12":
                    case "CP14":
                        eventEtudiant.EtudiantNote += 20.00;
                        break;
                    case "CO2":
                        eventEtudiant.EtudiantNote -= 0.50;
                        break;
                    case "TP5":
                    case "CO3":
                        eventEtudiant.EtudiantNote -= 1.00;
                        break;
                    case "TP15":
                        eventEtudiant.EtudiantNote -= 2.00;
                        break;
                    default:
                        break;
                }

                App.noteEditEvents.Remove(selectedEvent);
                App.etudiants[eventEtudiantIndex] = eventEtudiant;
                File.WriteAllText(Path.Combine(App.savedFilesFolderPath, "students.json"), JsonSerializer.Serialize(App.etudiants));
                File.WriteAllText(Path.Combine(App.savedFilesFolderPath, "note_edit_events.json"), JsonSerializer.Serialize(App.noteEditEvents));
                ViewModel.OnNavigatedTo(null);
            }
            else
            {
                ContentDialog nullStudentDialog = new ContentDialog()
                {
                    XamlRoot = App.MainWindow.Content.XamlRoot,
                    Title = "LOSPAÉ",
                    Content = "L'étudiant " + selectedEvent.EtudiantName + " n'existe pas dans la base de données.",
                    CloseButtonText = "OK",
                    DefaultButton = ContentDialogButton.Close
                };
            }
        }
    }
}
