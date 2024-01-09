using System.Collections.ObjectModel;

using CommunityToolkit.Mvvm.ComponentModel;

using LOSPAÉ.Contracts.ViewModels;
using LOSPAÉ.Core.Contracts.Services;

namespace LOSPAÉ.ViewModels;

public partial class NotesViewModel : ObservableRecipient, INavigationAware
{
    public ObservableCollection<Etudiant> Source { get; } = new ObservableCollection<Etudiant>();

    public async void OnNavigatedTo(object parameter)
    {
        Source.Clear();

        foreach (var item in App.etudiants)
        {
            Source.Add(item);
        }
    }

    public void OnNavigatedFrom()
    {
    }
}
