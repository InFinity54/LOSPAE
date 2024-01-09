using System.Text.Json;
using LOSPAÉ.Core.Helpers;
using LOSPAÉ.ViewModels;
using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;
using Microsoft.VisualBasic.FileIO;
using Windows.ApplicationModel.Store;
using Windows.Storage.Pickers;
using WinRT.Interop;

namespace LOSPAÉ.Views;

public sealed partial class SettingsPage : Page
{
    public SettingsViewModel ViewModel
    {
        get;
    }

    public SettingsPage()
    {
        ViewModel = App.GetService<SettingsViewModel>();
        InitializeComponent();
        // TODO: Ajouter un avertissement sur l'encodage du fichier CSV (UTF-8 obligatoire).
        // TODO: Ajouter le chemin vers le dossier de configuration dans la page des paramètres.
    }

    private async void CsvDataImport_Click(object sender, RoutedEventArgs e)
    {
        CsvDataImport.IsEnabled = false;
        CsvDataImportProgress.IsActive = true;

        var picker = new FileOpenPicker();
        picker.ViewMode = PickerViewMode.List;
        picker.SuggestedStartLocation = PickerLocationId.ComputerFolder;
        picker.FileTypeFilter.Add(".csv");

        var WindowHandle = WindowNative.GetWindowHandle(App.MainWindow);
        InitializeWithWindow.Initialize(picker, WindowHandle);

        Windows.Storage.StorageFile file = await picker.PickSingleFileAsync();

        if (file != null)
        {
            App.etudiants.Clear();
            App.noteEditEvents.Clear();

            using (TextFieldParser parser = new TextFieldParser(file.Path, System.Text.Encoding.UTF8))
            {
                parser.TextFieldType = FieldType.Delimited;
                parser.SetDelimiters(";");

                while (!parser.EndOfData)
                {
                    //Process row
                    string[] fields = parser.ReadFields();

                    if (fields[0] != "Etudiant" && fields[1] != "Spe")
                    {
                        App.etudiants.Add(new Etudiant(fields[0], fields[1], 20.00));
                    }
                }

                parser.Close();
                File.WriteAllText(Path.Combine(Windows.Storage.ApplicationData.Current.LocalFolder.Path, "students.json"), JsonSerializer.Serialize(App.etudiants));
                File.WriteAllText(Path.Combine(Windows.Storage.ApplicationData.Current.LocalFolder.Path, "note_edit_events.json"), JsonSerializer.Serialize(App.noteEditEvents));
            }

            ContentDialog dialog = new ContentDialog();

            // XamlRoot must be set in the case of a ContentDialog running in a Desktop app
            dialog.XamlRoot = this.XamlRoot;
            dialog.Style = Application.Current.Resources["DefaultContentDialogStyle"] as Style;
            dialog.Title = "Importation CSV";
            dialog.PrimaryButtonText = "OK";
            dialog.DefaultButton = ContentDialogButton.Primary;
            dialog.Content = "Le fichier \"" + file.Name + "\" a été importé avec succès.";

            await dialog.ShowAsync();
        }

        CsvDataImport.IsEnabled = true;
        CsvDataImportProgress.IsActive = false;
    }
}