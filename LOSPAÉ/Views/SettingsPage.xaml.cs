using System.Text.Json;
using LOSPAÉ.Core.Helpers;
using LOSPAÉ.ViewModels;
using Microsoft.UI.Xaml;
using Microsoft.UI.Xaml.Controls;
using Microsoft.VisualBasic.FileIO;
using Windows.ApplicationModel.DataTransfer;
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
        ConfigFolderPath.Text = App.savedFilesFolderPath;
        AppVersionDisplay.Text = "Version " + App.version;
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
                File.WriteAllText(Path.Combine(App.savedFilesFolderPath, "students.json"), JsonSerializer.Serialize(App.etudiants));
                File.WriteAllText(Path.Combine(App.savedFilesFolderPath, "note_edit_events.json"), JsonSerializer.Serialize(App.noteEditEvents));
            }

            ContentDialog dialog = new ContentDialog();
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

    private void ConfigFolderCopyButton_Click(object sender, RoutedEventArgs e)
    {
        var package = new DataPackage();
        package.SetText(ConfigFolderPath.Text);
        Clipboard.SetContent(package);
    }

    private async void ConfigFolderChangeButton_Click(object sender, RoutedEventArgs e)
    {
        var picker = new FolderPicker();
        picker.SuggestedStartLocation = PickerLocationId.ComputerFolder;
        picker.FileTypeFilter.Add("*");

        var WindowHandle = WindowNative.GetWindowHandle(App.MainWindow);
        InitializeWithWindow.Initialize(picker, WindowHandle);

        Windows.Storage.StorageFolder folder = await picker.PickSingleFolderAsync();

        if (folder != null)
        {
            App.savedFilesFolderPath = folder.Path;
            ConfigFolderPath.Text = folder.Path;
            File.WriteAllText(Path.Combine(App.appSettingsFilesFolderPath, "saved_files_location.txt"), folder.Path);

            ContentDialog dialog = new ContentDialog();
            dialog.XamlRoot = this.XamlRoot;
            dialog.Style = Application.Current.Resources["DefaultContentDialogStyle"] as Style;
            dialog.Title = "Dossier de stockage de la configuration";
            dialog.PrimaryButtonText = "OK";
            dialog.DefaultButton = ContentDialogButton.Primary;
            dialog.Content = "Le dossier de stockage de la configuration a été correctement modifié." + Environment.NewLine + "Le nouveau dossier est le suivant : \"" + folder.Path + "\"" + Environment.NewLine + "L'application va maintenant redémarrer. Déplacez les fichiers de configuration depuis l'ancien vers le nouveau dossier pour conserver les données, ou redémarrez le programme pour repartir de zéro.";
            await dialog.ShowAsync();
        }
    }
}